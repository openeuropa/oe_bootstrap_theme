<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel\fixtures;

use Drupal\oe_bootstrap_theme\ValueObject\FileValueObject;

/**
 * Transforms test data coming from YAML files.
 *
 * The code is not implemented in a test module to avoid altering the chain of
 * preprocess hooks.
 */
final class PatternTestDataMassager {

  /**
   * Massages test data for a pattern.
   *
   * @param string $patternName
   *   The pattern name.
   * @param array $data
   *   The data structure.
   *
   * @return array
   *   The massaged data structure.
   */
  public static function massageData(string $patternName, array $data): array {
    $callable = [
      self::class,
      'massage' . self::snakeToCamel($patternName) . 'Pattern',
    ];

    if (is_callable($callable)) {
      $data = call_user_func($callable, $data);
    }

    return $data;
  }

  /**
   * Massages data for the "file" pattern.
   *
   * @param array $data
   *   The data structure.
   *
   * @return array
   *   The massaged data structure.
   */
  private static function massageFilePattern(array $data): array {
    $data['#fields']['file'] = FileValueObject::fromArray($data['#fields']['file']);

    if (!empty($data['#fields']['translations'])) {
      foreach ($data['#fields']['translations'] as $index => $translation) {
        $data['#fields']['translations'][$index] = FileValueObject::fromArray($translation);
      }
    }

    return $data;
  }

  /**
   * Transforms a string from snake_case to CamelCase.
   *
   * @param string $string
   *   The string in snake case.
   *
   * @return string
   *   The string in camel case.
   */
  private static function snakeToCamel(string $string): string {
    return strtr(ucwords($string, '_'), ['_' => '']);
  }

}
