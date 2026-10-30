<?php

declare(strict_types=1);

namespace Drupal\oe_bootstrap_theme_sdc_port\Twig;

use Drupal\Core\Render\Markup;
use Drupal\oe_bootstrap_theme\ValueObject\FileValueObject;
use Drupal\oe_bootstrap_theme\ValueObject\ImageValueObject;

/**
 * Provides functions to generate twig code.
 */
class TwigGenerator {

  protected const RESERVED = [
    'and', 'or', 'xor', 'not', 'in', 'is', 'matches', 'starts', 'ends', 'has',
    'some', 'every', 'same', 'divisible', 'true', 'false', 'null', 'none',
  ];

  /**
   * Generates a list of variable set statements.
   *
   * @param array<string, mixed> $values
   *   Variable values by variable name.
   *
   * @return string
   *   The twig snippet with the set statements.
   */
  public function setTwigVariables(array $values): string {
    $twig = '';
    foreach ($values as $key => $value) {
      $expression = $this->toTwigExpression($value);
      $twig .= "{% set $key = $expression %}\n";
    }
    return $twig;
  }

  /**
   * Exports a value as a twig expression, recursively.
   *
   * @param mixed $value
   *   The value to export.
   * @param int $level
   *   The indentation level.
   *
   * @return string
   *   The resulting twig expression, as string.
   */
  public function toTwigExpression(mixed $value, int $level = 0): string {
    switch (TRUE) {
      case $value instanceof ImageValueObject:
        $image_defaults = ['alt' => '', 'name' => '', 'responsive' => TRUE];
        $expression = $this->toTwigExpression(array_filter(
          $value->getArray(),
          fn ($v, $k) => $v !== ($image_defaults[$k] ?? new \stdClass()), ARRAY_FILTER_USE_BOTH,
        ), $level);
        return $expression . '|to_image_value_object';

      case $value instanceof FileValueObject:
        $expression = $this->toTwigExpression($value->getArray(), $level);
        return $expression . '|to_file_value_object';

      case $value instanceof Markup:
        return $this->toTwigExpression(['#markup' => $value->__toString()], $level);

      case $value === NULL:
        return 'null';

      case is_bool($value):
        return $value ? 'true' : 'false';

      case is_int($value):
        return (string) $value;

      case is_float($value):
        if (!is_finite($value)) {
          throw new \InvalidArgumentException('INF/NAN cannot be expressed in Twig');
        }
        $s = var_export($value, TRUE);
        return str_contains($s, '.') || str_contains($s, 'E') ? $s : $s . '.0';

      case is_string($value):
        return $this->quote($value);

      case is_array($value):
        if ($value === []) {
          return '[]';
        }
        $pad = str_repeat('  ', $level + 1);
        $isList = array_is_list($value);
        $lines = [];
        foreach ($value as $k => $v) {
          $lines[] = $pad . ($isList ? '' : $this->key($k) . ': ') . $this->toTwigExpression($v, $level + 1);
        }
        [$open, $close] = $isList ? ['[', ']'] : ['{', '}'];
        return $open . "\n" . implode(",\n", $lines) . ",\n" . str_repeat('  ', $level) . $close;

      default:
        throw new \InvalidArgumentException('Unsupported type: ' . get_debug_type($value));
    }
  }

  /**
   * Formats an associative array key.
   *
   * @param int|string $key
   *   They key.
   *
   * @return string
   *   The formatted key.
   */
  protected function key(int|string $key): string {
    if (is_int($key)) {
      return $key < 0 ? "($key)" : (string) $key;
    }
    if (
      preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key) === 1 &&
      !in_array(strtolower($key), self::RESERVED, TRUE)
    ) {
      return $key;
    }
    return $this->quote($key);
  }

  /**
   * Quotes a string value or an associative array key.
   *
   * @param string $string
   *   The value to quote.
   *
   * @return string
   *   The quoted value expression.
   */
  protected function quote(string $string): string {
    return "'" . strtr($string, ['\\' => '\\\\', "'" => "\\'"]) . "'";
  }

}
