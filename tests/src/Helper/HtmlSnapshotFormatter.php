<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Helper;

/**
 * Provides methods to format html for a test snapshot.
 */
class HtmlSnapshotFormatter {

  private const VOID_TAGS = [
    'area', 'base', 'br', 'col', 'embed', 'hr', 'img',
    'input', 'link', 'meta', 'param', 'source', 'track', 'wbr',
  ];

  private const WHITESPACE_PRESERVING_TAGS = [
    'pre', 'textarea', 'script', 'style',
  ];

  private const BOOLEAN_ATTRIBUTES = [
    'allowfullscreen',
    'async',
    'autofocus',
    'autoplay',
    'checked',
    'controls',
    'defer',
    'disabled',
    'formnovalidate',
    'hidden',
    'inert',
    'ismap',
    'itemscope',
    'loop',
    'multiple',
    'muted',
    'nomodule',
    'novalidate',
    'open',
    'playsinline',
    'readonly',
    'required',
    'reversed',
    'selected',
  ];

  /**
   * Formats html for a test snapshot, similar to how jest does it.
   *
   * @param string $html
   *   The original html.
   *
   * @return string
   *   The formatted html for the snapshot.
   */
  public function format(string $html): string {
    if (trim($html) === '') {
      return '';
    }
    $dom = new \DOMDocument('1.0', 'UTF-8');
    // Suppress warnings, because DOMDocument is a flawed tool.
    @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
    // Some elements like `<style>` are rendered in `<head>` by default.
    $root = $dom->getElementsByTagName('body')->item(0)
      ?? $dom->getElementsByTagName('head')->item(0);
    assert($root instanceof \DOMElement);

    return $this->formatElementChildren($root, '');
  }

  /**
   * Formats element children.
   *
   * @param \DOMElement $element
   *   A DOM element.
   * @param string $indent
   *   Indentation.
   *
   * @return string
   *   Formatted html for the snapshot.
   */
  protected function formatElementChildren(\DOMElement $element, string $indent): string {
    $output = '';
    foreach ($element->childNodes as $childNode) {
      if ($childNode instanceof \DOMElement) {
        $output .= $this->formatElement($childNode, $indent);
      }
      elseif ($childNode instanceof \DOMText) {
        $text = $childNode->data;
        // Skip empty text nodes, which could be left over from  a processing
        // operation.
        if ($text === '') {
          continue;
        }
        if (trim($text) === '') {
          $output .= "\n";
          continue;
        }
        $text = preg_replace('#^\s+|\s+$#', "\n", $childNode->data);
        $text = $indent . str_replace("\n", "\n$indent", $text) . "\n";
        $text = preg_replace("# +\n#", "\n", $text);
        $output .= htmlspecialchars($text, ENT_NOQUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
      }
      else {
        \assert($childNode instanceof \DOMNode);
        $output .= $indent . $childNode->ownerDocument->saveHTML($childNode) . "\n";
      }
    }
    return $output;
  }

  /**
   * Formats a DOM element for a snapshot.
   *
   * @param \DOMElement $element
   *   The element.
   * @param string $indent
   *   The indentation.
   *
   * @return string
   *   The formatted html for the snapshot.
   */
  protected function formatElement(\DOMElement $element, string $indent): string {
    if (in_array($element->tagName, self::WHITESPACE_PRESERVING_TAGS)) {
      return $element->ownerDocument->saveHTML($element);
    }
    $output = $indent . '<' . $element->tagName;
    if ($element->hasAttributes()) {
      $output .= "\n";
      foreach ($element->attributes as $attribute) {
        if (in_array($attribute->name, self::BOOLEAN_ATTRIBUTES)) {
          if ($attribute->name === $attribute->value) {
            $output .= $indent . '  ' . $attribute->name . "\n";
            continue;
          }
          if ($attribute->value === '' && !static::isBooleanAttributeInPhp($attribute->name)) {
            $output .= $indent . '  ' . $attribute->name . "\n";
            continue;
          }
        }
        $output .= sprintf(
          '%s  %s="%s"' . "\n",
          $indent,
          $attribute->name,
          htmlspecialchars(
            $attribute->value,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
          ),
        );
      }
      $output .= $indent;
    }
    $children_output = $this->formatElementChildren($element, $indent . '  ');
    if ($children_output !== '') {
      return $output . ">\n" . $children_output . $indent . '</' . $element->tagName . '>' . "\n";
    }
    if (in_array($element->tagName, self::VOID_TAGS)) {
      // Correct HTML 5 would be '>', but having the '/>' is more clear in the
      // snapshot, and it aligns with jest snapshots in BCL.
      if (!str_ends_with($output, "\n") && !str_ends_with($output, ' ')) {
        // Insert a space, if there were no attributes, resulting in '<br />'
        // instead of '<br/>'. This aligns with jest snapshots in BCL.
        return $output . ' />' . "\n";
      }
      return $output . '/>' . "\n";
    }
    return $output . '></' . $element->tagName . '>' . "\n";
  }

  /**
   * Tests if DOMDocument considers an attribute as boolean.
   *
   * @param string $name
   *   The attribute name.
   *
   * @return bool
   *   TRUE for boolean, FALSE for non boolean.
   */
  protected static function isBooleanAttributeInPhp(string $name): bool {
    static $is_boolean_map = [];
    return $is_boolean_map[$name] ??= (function () use ($name): bool {
      $dom = new \DOMDocument('1.0', 'UTF-8');
      $dom->loadHTML("<a $name>");
      $element = $dom->getElementsByTagName('a')->item(0);
      assert($element instanceof \DOMElement);
      return $element->getAttribute($name) === $name;
    })();
  }

}
