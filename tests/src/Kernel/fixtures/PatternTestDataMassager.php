<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel\fixtures;

use Drupal\Core\Render\Markup;
use Drupal\oe_bootstrap_theme\ValueObject\FileValueObject;
use Drupal\oe_bootstrap_theme\ValueObject\ImageValueObject;

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
   * Massages data for the "card" pattern.
   *
   * @param array $data
   *   The data structure.
   *
   * @return array
   *   The massaged data structure.
   */
  private static function massageCardPattern(array $data): array {
    if (isset($data['#fields']['image'])) {
      $data['#fields']['image'] = ImageValueObject::fromArray($data['#fields']['image']);
    }

    return $data;
  }

  /**
   * Massages data for the "card_layout" pattern.
   *
   * @param array $data
   *   The data structure.
   *
   * @return array
   *   The massaged data structure.
   */
  private static function massageCardLayoutPattern(array $data): array {
    foreach ($data['#fields']['items'] as &$item) {
      if (isset($item['image'])) {
        $item['image'] = ImageValueObject::fromArray($item['image']);
      }
    }

    return $data;
  }

  /**
   * Massages data for the "listing" pattern.
   *
   * @param array $data
   *   The data structure.
   *
   * @return array
   *   The massaged data structure.
   */
  private static function massageListingPattern(array $data): array {
    foreach ($data['#fields']['items'] as &$item) {
      if (isset($item['image'])) {
        $item['image'] = ImageValueObject::fromArray($item['image']);
      }
    }

    return $data;
  }

  /**
   * Massages data for the "gallery" pattern.
   *
   * @param array $data
   *   The data structure.
   *
   * @return array
   *   The massaged data structure.
   */
  private static function massageGalleryPattern(array $data): array {
    foreach ($data['#fields']['items'] as &$item) {
      foreach (['thumbnail', 'media'] as $key) {
        if (isset($item[$key]['#markup'])) {
          $item[$key] = Markup::create($item[$key]['#markup']);
        }
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
