<?php

declare(strict_types=1);

namespace Drupal\oe_bootstrap_theme_sdc_port;

use Drupal\Component\Serialization\Yaml;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Yaml as SymfonyYaml;

/**
 * Provides yaml dumping with custom rules, especially for components.
 */
class NiceYamlDumper {

  /**
   * Dumps a component definition to yaml.
   *
   * @param array $definition
   *   The component definition.
   *
   * @return string
   *   The yaml.
   */
  public static function dumpComponent(array $definition): string {
    return NiceYamlDumper::dump($definition, static::dumpComponentNode(...));
  }

  /**
   * Dumps a single node in the component definition tree.
   *
   * @param mixed $value
   *   The node value.
   * @param list<array-key> $trail
   *   The array keys up to here.
   *
   * @return string|null
   *   The dumped value, or NULL to fall back to default yaml.
   */
  protected static function dumpComponentNode(mixed $value, array $trail): ?string {
    if (count($trail) < 3) {
      return NULL;
    }
    $trail_reverse = array_reverse($trail);
    if ($trail_reverse[2] !== 'properties') {
      return NULL;
    }
    $key = $trail_reverse[0];
    if ($key === 'type') {
      if (!is_array($value) || array_is_list($value)) {
        return static::dumpInline($value);
      }
    }
    return NULL;
  }

  /**
   * Dumps a valid as inline yaml.
   *
   * @param mixed $value
   *   The value to dump.
   *
   * @return string
   *   The dumped value.
   */
  public static function dumpInline(mixed $value): string {
    $dumper = new Dumper(2);
    return $dumper->dump($value, flags: SymfonyYaml::DUMP_EXCEPTION_ON_INVALID_TYPE | SymfonyYaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
  }

  /**
   * Dumps a string with forced single quotes.
   *
   * @param string $value
   *   The string to quote.
   *
   * @return string
   *   The yaml output.
   */
  public static function dumpQuoted(string $value): string {
    $yaml = Yaml::encode($value . ' ');
    if (!str_ends_with($yaml, "'")) {
      return $yaml;
    }
    return substr($yaml, 0, -2) . "'";
  }

  /**
   * Dumps a value with custom rules.
   *
   * @param mixed $value
   *   The value to dump.
   * @param callable(mixed, list<string>): (string|null) $fn_dump_node
   *   A callback that is called for each node, with custom rules.
   *
   * @return string
   *   The dumped yaml.
   */
  public static function dump(mixed $value, callable $fn_dump_node): string {
    $yaml_orig = Yaml::encode($value);
    $prefix = 'INLINE_REPLACE_';
    for ($i = 0; str_contains($yaml_orig, $prefix); ++$i) {
      $prefix = 'INLINE_REPLACE_' . $i . '_';
    }
    $index = 0;
    $replacements = [];
    $prep = function (mixed $arg, array $trail) use (&$replacements, &$prep, $fn_dump_node, $prefix, &$index) {
      $alt_arg_yaml = $fn_dump_node($arg, $trail);
      if ($alt_arg_yaml !== NULL) {
        $key = $prefix . $index;
        $replacements[$key] = $alt_arg_yaml;
        ++$index;
        return $key;
      }
      if (is_array($arg)) {
        foreach ($arg as $k => $v) {
          $arg[$k] = $prep($v, [...$trail, $k]);
        }
      }
      return $arg;
    };
    $value_with_placeholders = $prep($value, []);
    $yaml_with_placeholders = Yaml::encode($value_with_placeholders);
    $yaml_nice = strtr($yaml_with_placeholders, $replacements);
    $yaml_renormalized = Yaml::encode(Yaml::decode($yaml_nice));
    if ($yaml_renormalized !== $yaml_orig) {
      throw new \RuntimeException("Mismatch in yaml:\nEXPECTED:\n$yaml_orig\nACTUAL:\n$yaml_renormalized");
    }
    return $yaml_nice;
  }

}
