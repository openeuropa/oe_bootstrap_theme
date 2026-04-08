<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel\fixtures;

use Drupal\Core\Render\Markup;
use Drupal\oe_bootstrap_theme\ValueObject\FileValueObject;
use Drupal\oe_bootstrap_theme\ValueObject\ImageValueObject;
use Symfony\Component\Yaml\Tag\TaggedValue;

/**
 * Transforms test data coming from YAML files.
 *
 * The code is not implemented in a test module to avoid altering the chain of
 * preprocess hooks.
 */
final class PatternTestDataMassager {

  /**
   * Prepares a render element from yaml data.
   *
   * @param array $data
   *   The data from yaml, which does not contain any objects besides
   *   TaggedValue.
   *
   * @return array
   *   The massaged render element, with objects based on the TaggedValue itesm.
   */
  public static function massageDataRecursive(mixed $data): mixed {
    if (is_array($data)) {
      return array_map(self::massageDataRecursive(...), $data);
    }
    if (!$data instanceof TaggedValue) {
      return $data;
    }
    return match ($data->getTag()) {
      'Markup' => Markup::create($data->getValue()),
      'FileValueObject' => FileValueObject::fromArray($data->getValue()),
      'ImageValueObject' => ImageValueObject::fromArray($data->getValue()),
    };
  }

}
