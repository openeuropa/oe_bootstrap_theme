<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Helper;

use PHPUnit\Framework\Assert;

/**
 * Provides methods to "stabilize" noisy html.
 */
class HtmlSnapshotStabilizer {

  /**
   * Map of noise replacements.
   *
   * This is only ever modified in a cloned instance, to avoid statefulness.
   *
   * @var array<string, string>
   */
  private array $map = [];

  /**
   * Stabilizes html by inserting noise placeholders.
   *
   * @param string $html
   *   Original html.
   * @param string $variation_html
   *   A different version of the html, which differs in the "noisy" parts.
   *
   * @return string
   *   Stabilized html.
   */
  public function stabilize(string $html, string $variation_html): string {
    return (clone $this)->doStabilize($html, $variation_html);
  }

  /**
   * Stabilizes html by inserting noise placeholders.
   *
   * @param string $html
   *   The original html.
   * @param string $variation_html
   *   A different version of the html, which differs in the "noisy" parts.
   *
   * @return string
   *   The stabilized html.
   */
  private function doStabilize(string $html, string $variation_html): string {
    // @todo Detect if the original string contains '(#noise#)'.
    $parts = $this->splitHtml($html);
    $variation_parts = $this->splitHtml($variation_html);
    Assert::assertSameSize($parts, $variation_parts);
    $noise = array_diff($variation_parts, $parts);
    foreach ($noise as $i => $variation_part) {
      $parts[$i] = $this->stabilizePart($parts[$i], $variation_part);
    }
    return implode($parts);
  }

  /**
   * Stabilizes a part.
   *
   * @param string $part
   *   A part from ->splitParts().
   * @param string $variation_part
   *   The aligned part from the variation html.
   *
   * @return string
   *   The replacement with noise placeholder.
   */
  protected function stabilizePart(string $part, string $variation_part): string {
    $subparts = $this->splitPart($part);
    $variation_subparts = $this->splitPart($variation_part);
    $prefix = '';
    while (reset($subparts) === reset($variation_subparts)) {
      $prefix .= array_shift($subparts);
      array_shift($variation_subparts);
    }
    $suffix = '';
    while (end($subparts) === end($variation_subparts)) {
      $suffix = array_pop($subparts) . $suffix;
      array_pop($variation_subparts);
    }
    $key = implode($subparts) . '<>' . implode($variation_subparts);
    $replacement = $this->map[$key] ??= sprintf('(#*NOISE#%s*#)', count($this->map));
    return $prefix . $replacement . $suffix;
  }

  /**
   * Splits a piece of html into token-like parts.
   *
   * @param string $html
   *   The original html to split.
   *
   * @return list<string>
   *   The parts.
   */
  protected function splitHtml(string $html): array {
    return preg_split('#([">< ;,])#', $html, flags: PREG_SPLIT_DELIM_CAPTURE);
  }

  /**
   * Splits a part into smaller pieces.
   *
   * @param string $part
   *   A part from the ->splitHtml().
   *
   * @return list<string>
   *   Smaller pieces.
   */
  protected function splitPart(string $part): array {
    return preg_split('#([\-_\.])#', $part, flags: PREG_SPLIT_DELIM_CAPTURE);
  }

}
