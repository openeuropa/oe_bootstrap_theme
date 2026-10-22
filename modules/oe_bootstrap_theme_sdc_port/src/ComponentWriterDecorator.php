<?php

declare(strict_types=1);

namespace Drupal\oe_bootstrap_theme_sdc_port;

use Drupal\ui_patterns_to_sdc\ComponentWriter;

/**
 * Service override for the component writer.
 *
 * This is registered as a decorator, but technically it is not really.
 */
class ComponentWriterDecorator extends ComponentWriter {

  /**
   * {@inheritdoc}
   */
  public function writeDefinition(string $component_id, array $definition, string $component_path): void {
    $path = \realpath('.') . '/' . $component_path . '/';
    if (!\is_dir($path)) {
      \mkdir($path, 0775, TRUE);
    }
    $filename = $component_id . '.component.yml';
    // Let's comply with https://prettier.io/
    // - indentation: 2 spaces
    // However, still not 100% compliant:
    // - strings: Symfony does simple quotes, Prettier does double quotes
    // - empty objects: Symfony does "{  }", Prettier does "{}"
    // - arrays of objects: Symfony adds a line break after dash, Prettier
    //   doesn't.
    $yaml = NiceYamlDumper::dumpComponent($definition);

    \file_put_contents($path . $filename, $yaml);
  }

}
