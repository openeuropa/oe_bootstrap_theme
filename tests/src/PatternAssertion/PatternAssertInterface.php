<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\PatternAssertion;

/**
 * Interface implemented by all pattern assertion objects.
 *
 * @see openeuropa/oe_theme
 */
interface PatternAssertInterface {

  /**
   * Asserts that a rendered pattern is correct.
   *
   * @param array $expected
   *   An array of expected values, keyed by field name.
   * @param string $html
   *   The rendered pattern.
   */
  public function assertPattern(array $expected, string $html): void;

  /**
   * Asserts that a rendered pattern uses a variant.
   *
   * @param string $variant
   *   The variant to check for.
   * @param string $html
   *   The rendered pattern.
   */
  public function assertVariant(string $variant, string $html): void;

}
