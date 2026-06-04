<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\ComponentAssertion;

/**
 * Interface implemented by all SDC component assertion objects.
 */
interface ComponentAssertInterface {

  /**
   * Asserts that a rendered component is correct.
   *
   * @param array $expected
   *   An array of expected values, keyed by field name.
   * @param string $html
   *   The rendered component HTML.
   */
  public function assertComponent(array $expected, string $html): void;

  /**
   * Asserts that a rendered component uses a variant.
   *
   * @param string $variant
   *   The variant to check for.
   * @param string $html
   *   The rendered component HTML.
   */
  public function assertVariant(string $variant, string $html): void;

}
