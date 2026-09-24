<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Unit;

use Drupal\Component\Serialization\Yaml;
use Drupal\Tests\UnitTestCase;

/**
 * Tests that the nullability of component props matches their requiredness.
 *
 * A prop that is not listed in the 'required' key of its parent schema can be
 * omitted, but a caller that passes NULL explicitly - e.g. a Twig variable that
 * happens to be empty - will trigger a validation error, unless the schema
 * allows NULL. The other way around, a prop that is required has to be passed,
 * so allowing NULL only lets callers bypass the requirement.
 */
class OptionalPropsNullableTest extends UnitTestCase {

  /**
   * Report key for required props that do allow NULL.
   */
  protected const REPORT_REQUIRED_NULLABLE = 'required props that allow NULL';

  /**
   * Report key for optional props that do not allow NULL.
   */
  protected const REPORT_OPTIONAL_NON_NULLABLE = 'optional props that do not allow NULL';

  /**
   * Types known to json schema.
   *
   * Any other value in a 'type' key is a PHP class or interface name.
   *
   * @see \Drupal\Core\Theme\Component\ComponentValidator::getClassProps()
   */
  protected const JSON_SCHEMA_TYPES = [
    'array',
    'boolean',
    'integer',
    'null',
    'number',
    'object',
    'string',
  ];

  /**
   * Tests the nullability of all props of all components of the theme.
   */
  public function testPropsNullability(): void {
    $definitions = $this->findComponentDefinitions();
    $reports = [];
    ksort($definitions);
    foreach ($definitions as $id => $definition) {
      foreach ($this->findNullabilityMismatches($definition['props'] ?? [], '', TRUE) as $prop_path => $problem) {
        $reports[$problem][$id][] = $prop_path;
      }
    }

    if ($reports !== []) {
      $this->fail(Yaml::encode($reports));
    }
    else {
      $this->addToAssertionCount(1);
    }
  }

  /**
   * Finds all component definitions.
   *
   * @return array<string, array>
   *   Component definitions by component name.
   */
  protected function findComponentDefinitions(): array {
    $files = glob(dirname(__DIR__, 3) . '/components/**/*.component.yml');
    $this->assertNotEmpty($files);
    $definitions = [];
    foreach ($files as $file) {
      $name = 'oe_bootstrap_theme:' . basename($file, '.component.yml');
      $yaml = file_get_contents($file);
      $definitions[$name] = Yaml::decode($yaml);
    }
    return $definitions;
  }

  /**
   * Finds props whose nullability does not match whether they are required.
   *
   * @param array $schema
   *   A json schema fragment from a component definition.
   * @param string $path
   *   Path of the schema fragment, relative to the component 'props' schema.
   * @param bool $top_level
   *   TRUE if the schema fragment is the 'props' schema of a component, so that
   *   its properties are top-level props.
   *
   * @return \Generator<string, string>
   *   Problems found in properties, keyed by property path.
   */
  protected function findNullabilityMismatches(array $schema, string $path = '', bool $top_level = FALSE): \Generator {
    $required = $schema['required'] ?? [];
    foreach ($schema['properties'] ?? [] as $name => $prop_schema) {
      $prop_path = $path === '' ? (string) $name : $path . '.' . $name;
      $prop_problems = [];
      $is_nullable = $this->schemaAllowsNull($prop_schema, $top_level, $prop_problems);
      foreach ($prop_problems as $prop_problem) {
        yield $prop_path => $prop_problem;
      }
      $is_required = in_array($name, $required, TRUE);
      if ($is_nullable && $is_required) {
        yield $prop_path => self::REPORT_REQUIRED_NULLABLE;
      }
      elseif (!$is_nullable && !$is_required) {
        yield $prop_path => self::REPORT_OPTIONAL_NON_NULLABLE;
      }
      yield from $this->findNullabilityMismatches($prop_schema, $prop_path);
    }

    // Array items are not props themselves, but they can contain props.
    $items = $schema['items'] ?? NULL;
    if (is_array($items)) {
      if (array_is_list($items)) {
        // A list of schemas describes the items by position.
        foreach ($items as $delta => $item_schema) {
          yield from $this->findNullabilityMismatches($item_schema, $path . '[' . $delta . ']');
        }
      }
      else {
        // A single schema applies to all the items.
        yield from $this->findNullabilityMismatches($items, $path . '[]');
      }
    }

    // Props can also be nested in the branches of a composition keyword.
    foreach (['anyOf', 'oneOf', 'allOf'] as $keyword) {
      foreach ($schema[$keyword] ?? [] as $delta => $branch) {
        yield from $this->findNullabilityMismatches($branch, $path . '.' . $keyword . '[' . $delta . ']');
      }
    }
  }

  /**
   * Determines whether a schema fragment accepts a NULL value.
   *
   * @param array $schema
   *   A json schema fragment.
   * @param bool $top_level_prop
   *   TRUE if the schema describes a top-level prop of a component. Those are
   *   treated differently if they are typed with a PHP class or interface.
   * @param list<string> $problems
   *   A list that will be populated with problems, if applicable.
   *
   * @return bool
   *   TRUE if NULL is a valid value for this schema.
   */
  protected function schemaAllowsNull(array $schema, bool $top_level_prop = FALSE, ?array &$problems = []): bool {
    $type_allows_null = isset($schema['type'])
      ? $this->typeAllowsNull((array) $schema['type'], $top_level_prop)
      : NULL;

    // An enum that does not list NULL rejects NULL, whatever the type says.
    if (array_key_exists('enum', $schema)) {
      if (!in_array(NULL, $schema['enum'], TRUE)) {
        if ($type_allows_null === TRUE) {
          $problems[] = 'Type allows NULL, but enum does not.';
        }
        return FALSE;
      }
      if (!$type_allows_null) {
        $problems[] = 'Enum allows NULL, but type does not.';
      }
      if (isset($schema['meta:enum']) && !isset($schema['meta:enum'][''])) {
        $problems[] = 'Enum allows NULL, but meta:enum does not contain a label for empty string.';
      }
    }

    if ($type_allows_null === FALSE) {
      return FALSE;
    }

    // At least one branch of 'anyOf' or 'oneOf' has to accept NULL.
    foreach (['anyOf', 'oneOf'] as $keyword) {
      if (!isset($schema[$keyword])) {
        continue;
      }
      foreach ($schema[$keyword] as $branch) {
        if ($this->schemaAllowsNull($branch)) {
          continue 2;
        }
      }
      return FALSE;
    }

    // All branches of 'allOf' have to accept NULL.
    foreach ($schema['allOf'] ?? [] as $branch) {
      if (!$this->schemaAllowsNull($branch)) {
        return FALSE;
      }
    }

    // Nothing rejects NULL.
    return TRUE;
  }

  /**
   * Determines whether a list of schema types accepts a NULL value.
   *
   * @param array $types
   *   Types from the 'type' key of a schema fragment.
   * @param bool $top_level_prop
   *   TRUE if the types belong to a top-level prop of a component.
   *
   * @return bool
   *   TRUE if NULL is a valid value for these types.
   */
  protected function typeAllowsNull(array $types, bool $top_level_prop): bool {
    if (in_array('null', $types, TRUE)) {
      return TRUE;
    }
    // Top-level props typed exclusively with PHP classes or interfaces are not
    // validated by the json schema validator: it replaces both the value and
    // the type with NULL, after validating the value separately. So NULL is
    // already accepted, and adding 'null' to the type would make no difference.
    // This does not apply to nested props, which keep their original type.
    // @see \Drupal\Core\Theme\Component\ComponentValidator::validateClassProps()
    // @see \Drupal\Core\Theme\Component\ComponentValidator::nullifyClassPropsSchema()
    return $top_level_prop && $types !== [] && !array_intersect($types, self::JSON_SCHEMA_TYPES);
  }

}
