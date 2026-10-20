<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel\fixtures;

/**
 * Populates omitted top-level optional component props with explicit NULL.
 */
final class OptionalPropsNullifier {

  /**
   * Populates missing optional props without changing nested input data.
   *
   * Nested NULL values are not necessarily equivalent to omitted keys: BCL
   * selects item types by key presence, and explicit icon paths override
   * defaults even when NULL. Those cases need their own assertions.
   *
   * @param array $schema
   *   The component props schema.
   * @param array $props
   *   The existing props from a rendering fixture.
   *
   * @return array
   *   Props with missing top-level optional properties set to NULL.
   */
  public static function populate(array $schema, array $props): array {
    foreach (array_keys($schema['properties'] ?? []) as $name) {
      if (!array_key_exists($name, $props) && !in_array($name, $schema['required'] ?? [], TRUE)) {
        $props[$name] = NULL;
      }
    }
    return $props;
  }

}
