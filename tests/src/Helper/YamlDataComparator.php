<?php

declare(strict_types=1);
namespace Drupal\Tests\oe_bootstrap_theme\Helper;

use Symfony\Component\Yaml\Tag\TaggedValue;

class YamlDataComparator {

  public function detectNoise(mixed $expected, mixed $actual): mixed {
    $missing = new \stdClass();
    return $this->compare(
      $expected,
      $actual,
      function (mixed $exp, mixed $act, array $path) {
        if (get_debug_type($exp) === get_debug_type($act)) {
          return new TaggedValue('noise', get_debug_type($exp));
        }
        return $act;
      },
      $missing,
      [],
    );
  }

  public function compare(mixed $expected, mixed $actual, callable $callback, object $missing, array $path): mixed {
    if ($expected === $actual) {
      return $expected;
    }
    if (get_debug_type($expected) !== get_debug_type($actual)) {
      return $callback($expected, $actual, $path);
    }
    if (is_array($expected)) {
      assert(is_array($actual));
      $result = [];
      foreach (array_diff_key($expected, $actual) as $key => $value) {
        $result[$key] = $callback($value, $missing, [...$path, $key]);
      }
      foreach (array_diff_key($actual, $expected) as $key => $value) {
        $result[$key] = $callback($missing, $value, [...$path, $key]);
      }
      foreach (array_intersect_key($expected, $actual) as $key => $value) {
        $result[$key] = $this->compare($value, $actual[$key], $callback, $missing, [...$path, $key]);
      }
      foreach (array_keys($result, $missing, TRUE) as $key_to_remove) {
        unset($result[$key_to_remove]);
      }
      ksort($result);
      return $result;
    }
    if ($expected instanceof TaggedValue) {
      assert($actual instanceof TaggedValue);
      if ($expected->getTag() !== $actual->getTag()) {
        return $callback($expected, $actual, $path);
      }
      return new TaggedValue(
        $expected->getTag(),
        $this->compare($expected->getValue(), $actual->getValue(), $callback, $missing, [...$path, '!']),
      );
    }
    if (is_object($expected)) {
      assert(is_object($actual));
      throw new \InvalidArgumentException(sprintf('Only TaggedValue is allowed, found %s', get_debug_type($expected)));
    }
    return $callback($expected, $actual, $path);
  }

}
