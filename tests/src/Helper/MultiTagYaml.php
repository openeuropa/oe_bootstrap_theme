<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Helper;

use Drupal\Component\Serialization\SerializationInterface;
use Drupal\Component\Serialization\Yaml;
use Symfony\Component\Yaml\Tag\TaggedValue;

/**
 * Provides yaml serialization with support for nested TaggedValue objects.
 *
 * This is needed because symfony yaml does not supported it.
 *
 * @see \Drupal\Component\Serialization\Yaml
 */
class MultiTagYaml implements SerializationInterface {

  /**
   * {@inheritdoc}
   */
  public static function encode($data): string {
    $data = static::collapseYamlTagsRecursive($data);
    return Yaml::encode($data);
  }

  /**
   * {@inheritdoc}
   */
  public static function decode($raw) {
    $data = Yaml::decode($raw);
    return static::expandYamlTagsRecursive($data);
  }

  /**
   * {@inheritdoc}
   */
  public static function getFileExtension() {
    return Yaml::getFileExtension();
  }

  /**
   * Collapses stacked TaggedValue objects.
   *
   * @param mixed $value
   *   Original data which may have nested TaggedValue objects.
   *   E.g. `new TaggedValue('a', new TaggedValue('b', 'V'))`.
   *
   * @return mixed
   *   Data with collapsed TaggedValue objects.
   *   E.g. `new TaggedValue('a!b', 'V')`.
   */
  private static function collapseYamlTagsRecursive(mixed $value): mixed {
    if (is_array($value)) {
      return array_map(static::collapseYamlTagsRecursive(...), $value);
    }
    if (!$value instanceof TaggedValue) {
      return $value;
    }
    $tag = $value->getTag();
    if (str_contains($tag, '!')) {
      throw new \RuntimeException("Cannot collapse tags if the original tag already contains '!'.");
    }
    $inner = $value->getValue();
    while ($inner instanceof TaggedValue) {
      if (str_contains($inner->getTag(), '!')) {
        throw new \RuntimeException("Cannot collapse tags if the original tag already contains '!'.");
      }
      $tag .= '!' . $inner->getTag();
      $inner = $inner->getValue();
      assert(!is_object($inner));
    }
    $inner = static::collapseYamlTagsRecursive($inner);
    return new TaggedValue($tag, $inner);
  }

  /**
   * Expands TaggedValue objects to recreate the stacks.
   *
   * @param mixed $value
   *   Packed data.
   *
   * @return mixed
   *   Data with stacked TaggedValue objects unpacked.
   */
  private static function expandYamlTagsRecursive(mixed $value): mixed {
    if (is_array($value)) {
      return array_map(static::expandYamlTagsRecursive(...), $value);
    }
    if (!$value instanceof TaggedValue) {
      return $value;
    }
    $tags = explode('!', $value->getTag());
    $expanded = $value->getValue();
    foreach (array_reverse($tags) as $tag) {
      $expanded = new TaggedValue($tag, $expanded);
    }
    return $expanded;
  }

}
