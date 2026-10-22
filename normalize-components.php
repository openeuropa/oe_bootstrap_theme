<?php

/**
 * @file
 * A script to normalize existing components that were created manually.
 */

declare(strict_types=1);

use Composer\Autoload\ClassLoader;
use Drupal\Component\Serialization\Yaml;
use Drupal\oe_bootstrap_theme_sdc_port\NiceYamlDumper;

(function () {
  $class_loader = require_once __DIR__ . '/vendor/autoload.php';
  assert($class_loader instanceof ClassLoader);
  $class_loader->addPsr4('Drupal\\oe_bootstrap_theme_sdc_port\\', __DIR__ . '/modules/oe_bootstrap_theme_sdc_port/src');
})();

(function () {
  $components_dir = __DIR__ . '/components';
  $patterns_dir = __DIR__ . '/templates/patterns';

  $process_prop = function (array $prop, string $name): array {
    unset($prop['items']);
    unset($prop['properties']);
    // Reorder keys.
    $prop = [
      ...array_intersect_key(array_fill_keys([
        'title',
        'description',
      ], TRUE), $prop),
      ...$prop,
    ];
    return $prop;
  };

  foreach (scandir($components_dir) as $name) {
    if (!preg_match('#^(\w+)#', $name)) {
      continue;
    }
    if (!is_dir("$components_dir/$name")) {
      continue;
    }
    $component_yaml_file = "$components_dir/$name/$name.component.yml";
    if (!is_file($component_yaml_file)) {
      throw new \Exception("Missing file '$component_yaml_file'.");
    }
    $pattern_yaml_file = "$patterns_dir/$name/$name.ui_patterns.yml";
    if (!is_file($pattern_yaml_file)) {
      throw new \Exception("Missing file '$pattern_yaml_file'.");
    }
    $component_yaml = file_get_contents($component_yaml_file);
    $definition = Yaml::decode($component_yaml);
    $pattern_yaml = file_get_contents($pattern_yaml_file);
    $pattern_yaml_data = Yaml::decode($pattern_yaml);
    if (array_keys($pattern_yaml_data) !== [$name]) {
      throw new \Exception("Unexpected pattern names in '$pattern_yaml_file'.");
    }
    $pattern_definition = $pattern_yaml_data[$name];

    // Sort by order of original pattern fields and settings.
    $pattern_key_order = array_fill_keys(array_unique([
      ...array_keys($pattern_definition['settings'] ?? []),
      ...array_keys($pattern_definition['fields'] ?? []),
    ]), TRUE);
    $ksort = function (array &$assoc) use ($pattern_key_order): void {
      $assoc = [
        ...array_intersect_key($pattern_key_order, $assoc),
        ...$assoc,
      ];
    };

    $def_unsorted = $definition;
    if (!empty($definition['props']['properties'])) {
      $ksort($definition['props']['properties']);
      foreach ($definition['props']['properties'] as $prop_name => $prop) {
        $definition['props']['properties'][$prop_name] = $process_prop($prop, $prop_name);
      }
    }

    if (!empty($definition['slots'])) {
      $ksort($definition['slots']);
    }

    print "$name: ";
    print $definition === $def_unsorted ? 'NOT REORDERED' : 'REORDERED';
    print " -> $component_yaml_file\n";

    $yaml_normalized = NiceYamlDumper::dumpComponent($definition);
    $success = file_put_contents($component_yaml_file, $yaml_normalized);
    if (file_get_contents($component_yaml_file) !== $yaml_normalized) {
      throw new \Exception("Failed to write '$component_yaml_file'.");
    }
  }
})();
