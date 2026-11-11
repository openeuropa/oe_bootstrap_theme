<?php

declare(strict_types=1);

namespace Drupal\ui_patterns_to_sdc;

/**
 * Convert UI Patterns settings to JSON schema.
 */
class PropConverter {

  /**
   * Convert prop.
   */
  public function convert(array $setting): array {
    return match ($setting['type']) {
      'attributes' => [
        '$ref' => 'ui-patterns://attributes',
      ],
      'boolean' => [
        'type' => 'boolean',
      ],
      'checkboxes' => $this->convertCheckboxes($setting),
      'links' => [
        '$ref' => 'ui-patterns://links',
      ],
      'identifier' => [
        '$ref' => 'ui-patterns://identifier',
      ],
      'number' => $this->convertNumber($setting),
      'radios' => $this->convertEnum($setting),
      'select' => $this->convertEnum($setting),
      'textfield' => [
        'type' => 'string',
      ],
      'token' => [
        'type' => 'string',
      ],
      'url' => [
        '$ref' => 'ui-patterns://url',
      ],
      default => [],
    };
  }

  /**
   * Convert checkboxes.
   */
  private function convertCheckboxes(array $setting): array {
    $values = \array_keys($setting['options']);
    $labels = \array_values($setting['options']);
    $prop = [
      'type' => 'array',
      'uniqueItems' => TRUE,
      'items' => [
        'type' => $this->getEnumType($values),
        'enum' => $values,
      ],
    ];
    if (!empty(\array_diff($values, $labels))) {
      $prop['items']['meta:enum'] = $setting['options'];
    }
    return $prop;
  }

  /**
   * Convert select and radios.
   */
  private function convertEnum(array $setting): array {
    $values = \array_keys($setting['options']);
    $labels = \array_values($setting['options']);
    $prop = [
      'type' => $this->getEnumType($values),
      'enum' => $values,
    ];
    if (!empty(\array_diff($values, $labels))) {
      $prop['meta:enum'] = $setting['options'];
    }
    return $prop;
  }

  /**
   * Get enumeration type by checking the values.
   */
  private function getEnumType(array $values): array|string {
    $types = [];
    foreach ($values as $value) {
      if (\is_int($value)) {
        $types[] = 'integer';
        continue;
      }
      if (\is_float($value)) {
        $types[] = 'number';
        continue;
      }
      $types[] = 'string';
    }
    $types = \array_unique($types);
    if (\count($types) === 1) {
      return $types[0];
    }
    return $types;
  }

  /**
   * Convert number.
   */
  private function convertNumber(array $setting): array {
    $prop = [
      'type' => 'number',
    ];
    if (isset($setting['min'])) {
      $prop['minimum'] = $setting['min'];
    }
    if (isset($setting['max'])) {
      $prop['maximum'] = $setting['max'];
    }
    return $prop;
  }

}
