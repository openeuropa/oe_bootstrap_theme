<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Helper;

use PHPUnit\Framework\Assert;

/**
 * Provides methods to normalize html for a snapshot.
 */
class HtmlSnapshotNormalizer {

  private const WHITESPACE_PRESERVING_TAGS = [
    'pre', 'textarea', 'script', 'style',
  ];

  private const INLINE_TAGS = [
    'a', 'abbr', 'b', 'bdi', 'bdo', 'br',
    'cite', 'code', 'data', 'del', 'dfn',
    'em', 'i', 'img', 'ins', 'kbd',
    'label', 'mark', 'q', 's', 'samp',
    'small', 'span', 'strong', 'sub', 'sup',
    'time', 'u', 'var', 'wbr',

    'audio', 'button', 'canvas', 'iframe',
    'input', 'meter', 'output', 'progress',
    'select', 'svg', 'textarea', 'video',
  ];

  /**
   * Callbacks to invoke for every DOM element.
   *
   * @var list<callable(\DOMElement): void>
   */
  private array $elementCallbacks = [];

  /**
   * Immutable setter to add an element callback.
   *
   * @param callable(\DOMElement): void $callback
   *   A callback which is invoked for every DOM element.
   *
   * @return static
   *   A new instance with the callback added.
   */
  public function withElementCallback(callable $callback): static {
    $clone = clone $this;
    $clone->elementCallbacks[] = $callback;
    return $clone;
  }

  /**
   * Normalizes html for a snapshot.
   *
   * @param string $html
   *   The original html.
   *
   * @return string
   *   The normalized html.
   */
  public function normalize(string $html): string {
    if (trim($html) === '') {
      return '';
    }
    $dom = new \DOMDocument('1.0', 'UTF-8');
    // Suppress warnings, because DOMDocument is a flawed tool.
    @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
    // Some elements like `<style>` are rendered in `<head>` by default.
    $root = $dom->getElementsByTagName('body')->item(0)
      ?? $dom->getElementsByTagName('head')->item(0)
      ?? throw new \InvalidArgumentException("Bad html:\n" . $html);
    assert($root instanceof \DOMElement);

    // Normalize whitespace.
    $this->normalizeElementWhitespaceRecursive($root);

    // Process all elements within <body>.
    $xpath = new \DOMXPath($dom);
    $elements = $xpath->query('.//*', $root);
    foreach ($elements as $element) {
      assert($element instanceof \DOMElement);
      foreach ($this->elementCallbacks as $callback) {
        $callback($element);
      }
      $this->normalizeElementAttributes($element);
    }

    // Return only the inner HTML of body.
    $normalized_html = '';
    foreach ($root->childNodes as $child) {
      $normalized_html .= $dom->saveHTML($child);
    }
    return $normalized_html;
  }

  /**
   * Normalizes element attributes.
   *
   * @param \DOMElement $element
   *   A DOM element that will be modified.
   */
  protected function normalizeElementAttributes(\DOMElement $element): void {
    $attributes = [];
    foreach ($element->attributes as $attribute) {
      $attributes[$attribute->name] = $attribute->value;
    }
    ksort($attributes);
    foreach ($attributes as $name => $value) {
      $element->removeAttribute($name);
    }
    foreach ($attributes as $name => $value) {
      if ($name === 'class') {
        $classes = explode(' ', $value);
        sort($classes);
        $value = implode(' ', $classes);
      }
      $element->setAttribute($name, $value);
    }
  }

  /**
   * Normalizes whitespace in the element.
   *
   * @param \DOMElement $element
   *   The DOM element to modify.
   */
  protected function normalizeElementWhitespaceRecursive(\DOMElement $element): void {
    if (in_array($element->tagName, self::WHITESPACE_PRESERVING_TAGS, TRUE)) {
      return;
    }
    foreach ($element->childNodes as $child) {
      if ($child instanceof \DOMElement) {
        $this->normalizeElementWhitespaceRecursive($child);
      }
      elseif ($child instanceof \DOMText) {
        $text = $child->data;
        $text = preg_replace('/\s+/u', ' ', $text) ?? Assert::fail();
        if ($this->isBlockTag($child->previousSibling ?? $element)) {
          $text = ltrim($text, ' ');
        }
        if ($this->isBlockTag($child->nextSibling ?? $element)) {
          $text = rtrim($text, ' ');
        }
        $child->data = $text;
      }
    }
  }

  /**
   * Determines if the element is a block (non-inline) tag.
   *
   * @param \DOMNode $node
   *   The DOM element or node.
   *
   * @return bool
   *   TRUE for a block tag, FALSE for an inline tag.
   */
  protected function isBlockTag(\DOMNode $node): bool {
    return $node instanceof \DOMElement
      && !in_array($node->tagName, self::INLINE_TAGS, TRUE);
  }

}
