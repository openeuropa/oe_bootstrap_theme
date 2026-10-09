<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel\fixtures;

use Drupal\Core\Render\Element;

/**
 * Populates omitted optional component props with explicit NULL values.
 */
final class OptionalPropsNullifier {

  /**
   * Populates all omitted optional properties, preserving existing data.
   *
   * Nested properties are visited only when their parent is already present.
   * Array entries and render arrays are never replaced or populated.
   *
   * @param array $schema
   *   The component props schema.
   * @param array $props
   *   The existing props from a rendering fixture.
   *
   * @return array
   *   Props with missing optional properties set to NULL.
   */
  public static function populate(array $schema, array $props): array {
    foreach (self::missingPaths($schema, $props) as $path) {
      $props = self::withNull($props, $path);
    }
    return $props;
  }

  /**
   * Sets a single property to NULL without changing the original props.
   */
  private static function withNull(array $props, array $path): array {
    $key = array_shift($path);
    $props[$key] = $path === [] ? NULL : self::withNull($props[$key], $path);
    return $props;
  }

  /**
   * Finds missing optional properties in objects and existing array entries.
   *
   * @return \Generator<array>
   *   Paths represented as lists of property names and array indexes.
   */
  private static function missingPaths(array $schema, array $value, array $path = []): \Generator {
    if ($path !== [] && Element::isRenderArray($value)) {
      // Generators can return without yielding any values.
      // phpcs:ignore Drupal.Commenting.FunctionComment.InvalidReturnNotVoid
      return;
    }

    // Select composition branches relevant to the existing value. In
    // particular, do not apply object properties to a list of array items.
    $properties = $schema['properties'] ?? [];
    $required = $schema['required'] ?? [];
    $items = $schema['items'] ?? NULL;
    foreach (['allOf', 'anyOf', 'oneOf'] as $keyword) {
      foreach ($schema[$keyword] ?? [] as $branch) {
        $types = (array) ($branch['type'] ?? []);
        if ($types !== [] && !in_array('array', $types, TRUE) && !in_array('object', $types, TRUE)) {
          continue;
        }
        if ($value !== [] && array_is_list($value) && $types !== [] && !in_array('array', $types, TRUE)) {
          continue;
        }
        if (!array_is_list($value) && $types !== [] && !in_array('object', $types, TRUE)) {
          continue;
        }
        $properties = array_replace_recursive($properties, $branch['properties'] ?? []);
        $required = array_merge($required, $branch['required'] ?? []);
        $items = $branch['items'] ?? $items;
      }
    }

    foreach ($properties as $name => $prop_schema) {
      $prop_path = [...$path, $name];
      if (!array_key_exists($name, $value)) {
        if (!in_array($name, $required, TRUE)) {
          yield $prop_path;
        }
      }
      elseif (is_array($value[$name])) {
        yield from self::missingPaths($prop_schema, $value[$name], $prop_path);
      }
    }

    if (is_array($items)) {
      foreach ($value as $index => $item) {
        if (is_array($item)) {
          $item_schema = array_is_list($items) ? ($items[$index] ?? []) : $items;
          yield from self::missingPaths($item_schema, $item, [...$path, $index]);
        }
      }
    }
  }

}
