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

  /**
   * {@inheritdoc}
   */
  public function copyTemplates(string $component_path, array $legacy_definition): void {
    parent::copyTemplates($component_path, $legacy_definition);
    $component_id = $legacy_definition['id'];
    $target = \realpath('.') . '/' . $component_path . '/' . $component_id . '.twig';
    if (!is_file($target)) {
      return;
    }
    $twig = file_get_contents($target);

    // Replace 'pattern' with 'component' in strategic places.
    $twig = preg_replace('#' . sprintf(preg_quote('%s/**
 * @file
 * %s pattern.
'), '(', '.*)') . '#', "\$1 component.\n", $twig);
    file_put_contents($target, $twig);

    // Use twig-cs-fixer to format the twig file.
    exec(
      sprintf(
        'cd %s && ./vendor/bin/twig-cs-fixer fix %s',
        escapeshellarg(dirname(__DIR__, 3)),
        escapeshellarg($target),
      ),
      $output,
      $exit_code,
    );

    if ($exit_code !== 0) {
      throw new \RuntimeException(
        "Twig CS Fixer failed:\n" . implode("\n", $output)
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function writeStory(string $component_id, array $story, string $component_path): void {
    // Do not write the story.
  }

}
