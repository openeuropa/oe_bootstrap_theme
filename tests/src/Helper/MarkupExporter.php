<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Helper;

use PHPUnit\Framework\Assert;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Yaml\Tag\TaggedValue;

class MarkupExporter {

  private const INLINE_TAGS = [
    'a', 'abbr', 'b', 'bdi', 'bdo', 'cite', 'code',
    'em', 'i', 'label', 'q', 's', 'small', 'span',
    'strong', 'sub', 'sup', 'time', 'u',
  ];

  public function __construct(
    private readonly ?string $selector = NULL,
  ) {}

  public function export(string $html): array {
    # $document = new \DOMDocument();
    # $document->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    $crawler = new Crawler($html);
    $body = $crawler->filter('body')->first()->getNode(0);
    assert($body instanceof \DOMElement);
    $report = $this->exportElement($body);
    assert($report instanceof TaggedValue);
    return $report->getValue();
  }

  private function exportElement(\DOMElement $element): TaggedValue {
    $children = [];

    // Convert attributes to tagged values.
    $attribute_names = [];
    foreach ($element->attributes as $attribute) {
      $attr_value = $attribute->value;
      if ($attribute->name === 'class') {
        $attr_value = explode(' ', $attribute->value);
        sort($attr_value);
      }
      $children[] = new TaggedValue('attr:' . $attribute->name . ':', $attr_value);
      $attribute_names[] = $attribute->name;
    }
    array_multisort($attribute_names, $children);

    $rtrim_children = function () use (&$children) {
      $last_inserted = end($children);
      if (!is_string($last_inserted)) {
        return;
      }
      $last_inserted_trimmed = rtrim($last_inserted, "\n ");
      if ($last_inserted_trimmed === $last_inserted) {
        return;
      }
      array_pop($children);
      if ($last_inserted_trimmed !== '') {
        $children[] = $last_inserted_trimmed;
      }
    };

    $inline_parent = in_array($element->tagName, self::INLINE_TAGS);;
    $inline_child = NULL;
    // Convert child elements and text nodes recursively.
    foreach ($element->childNodes as $child) {
      if ($child instanceof \DOMElement) {
        $inline_child = in_array($child->tagName, self::INLINE_TAGS);
        if (!$inline_child) {
          $rtrim_children();
        }
        $children[] = $this->exportElement($child);
      }
      elseif ($child instanceof \DOMText) {
        $text = $child->textContent;
        if ($element->tagName !== 'pre') {
          $text = preg_replace('/\s+/u', ' ', $text) ?? Assert::fail();
        }
        if (!($inline_child ?? $inline_parent)) {
          $text = ltrim($text, "\n ");
        }
        if ($text !== '') {
          $children[] = $text;
        }
      }
    }

    if (!$inline_parent) {
      $rtrim_children();
    }

    return new TaggedValue('tag:' . $element->tagName . '/', $children);
  }

}
