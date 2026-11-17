<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel\fixtures;

use Drupal\Core\Render\Markup;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Url;
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
   * @param mixed $data
   *   The data from yaml, which does not contain any objects besides
   *   TaggedValue.
   * @param bool $use_objects
   *   TRUE, to return objects for `!AttributeOrArray`, `!DateTime` and `!Url`
   *   tags in the yaml.
   *   FALSE to return the plain values (array or string).
   *   Note that `!Attribute` tags are not impacted, they always return the
   *   Attribute object.
   *
   * @return mixed
   *   The massaged data, with values based on the TaggedValue items.
   */
  public static function massageDataRecursive(mixed $data, bool $use_objects = TRUE): mixed {
    if (is_array($data)) {
      return array_map(
        static fn (mixed $value): mixed => self::massageDataRecursive($value, $use_objects),
        $data,
      );
    }
    if (!$data instanceof TaggedValue) {
      return $data;
    }
    return match ($data->getTag()) {
      'Markup' => Markup::create($data->getValue()),
      'FileValueObject' => FileValueObject::fromArray($data->getValue()),
      'ImageValueObject' => ImageValueObject::fromArray($data->getValue()),
      'DateTime' => $use_objects ? new \DateTimeImmutable($data->getValue()) : $data->getValue(),
      'Attribute' => new Attribute($data->getValue()),
      'AttributeOrArray' => $use_objects ? new Attribute($data->getValue()) : $data->getValue(),
      'Url' => $use_objects ? Url::fromUri($data->getValue()) : Url::fromUri($data->getValue())->toString(),
      default => throw new \RuntimeException(sprintf('Unexpected tag %s.', $data->getTag()))
    };
  }

}
