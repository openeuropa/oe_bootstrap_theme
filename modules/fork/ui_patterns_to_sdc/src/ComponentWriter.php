<?php

declare(strict_types=1);

namespace Drupal\ui_patterns_to_sdc;

use Drupal\Component\Serialization\Yaml;
use Symfony\Component\Finder\Finder;

/**
 * Component writer.
 */
class ComponentWriter {

  /**
   * Write component definition to YML file.
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
    $yaml = Yaml::encode($definition);
    \file_put_contents($path . $filename, $yaml);
  }

  /**
   * Write component story to YML file.
   */
  public function writeStory(string $component_id, array $story, string $component_path): void {
    $path = \realpath('.') . '/' . $component_path . '/';
    if (!\is_dir($path)) {
      \mkdir($path, 0775, TRUE);
    }
    $filename = $component_id . '.preview.story.yml';
    $yaml = Yaml::encode($story);
    \file_put_contents($path . $filename, $yaml);
  }

  /**
   * Copy component assets to the new folder.
   */
  public function copyAssets(string $component_path, array $legacy_definition): void {
    $source = \realpath('.') . '/' . $legacy_definition['base path'];
    $target = \realpath('.') . '/' . $component_path . '/';
    $finder = new Finder();
    $finder->files()->notName('*.patterns.yml')->notName('*.pattern.yml')->notName('*.ui_patterns.yml')->notName('*.html.twig')->in($source);
    foreach ($finder as $file) {
      $path = \str_replace($source, $target, $file->getPath());
      if (!\is_dir($path)) {
        \mkdir($path, 0775, TRUE);
      }
      $from = $file->getPathname();
      $to = \str_replace($source, $target, $from);
      \copy($from, $to);
    }
  }

  /**
   * Copy component templates to the new locations.
   */
  public function copyTemplates(string $component_path, array $legacy_definition): void {
    $component_id = $legacy_definition['id'];
    // Copy pattern template to component folder.
    $source = \realpath('.') . '/' . $legacy_definition['base path'] . '/pattern-' . $component_id . '.html.twig';
    $target = \realpath('.') . '/' . $component_path . '/' . $component_id . '.twig';
    if (\file_exists($source)) {
      \copy($source, $target);
      return;
    }
    // Try with dash instead of underscores.
    $source = \realpath('.') . '/' . $legacy_definition['base path'] . '/pattern-' . \str_replace('_', '-', $component_id) . '.html.twig';
    if (\file_exists($source)) {
      \copy($source, $target);
    }
  }

  /**
   * Check if there is some preview or variant templates.
   */
  public function checkOtherTemplates(string $component_path, array $legacy_definition): void {
    $component_id = $legacy_definition['id'];
    $target = \realpath('.') . '/' . $component_path . '/' . $component_id . '.twig';
    $path = \realpath('.') . '/' . $legacy_definition['base path'] . '/pattern-' . str_replace('_', '-', $component_id) . '--preview.html.twig';
    if (\file_exists($path)) {
      print '⚠️ ' . $component_id . " has a preview template which will not be converted. Use the new stories system instead.\n";
    }
    // Example: pattern-button--variant-danger.html.twig.
    if (!isset($legacy_definition['variants']) || !\is_array($legacy_definition['variants'])) {
      return;
    }

    // Convert multiple template variations to an only template using
    // if/elseif/else constructs. Each existing template variant will become
    // a case in our main template and the content of the main template will
    // be kept in the `else` case.
    $new_template = '{% if variant == "';
    foreach (\array_keys($legacy_definition['variants']) as $variant_id) {
      $path = \realpath('.') . '/' . $legacy_definition['base path'] . '/pattern-' . $component_id . '--variant-' . $variant_id . '.html.twig';
      if (!\file_exists($path)) {
        // Try with dash instead of underscores.
        $path = \realpath('.') . '/' . $legacy_definition['base path'] . '/pattern-' . \str_replace('_', '-', $component_id) . '--variant-' . $variant_id . '.html.twig';
      }
      if (!\file_exists($path)) {
        continue;
      }

      $new_template .= $variant_id . '" %}' . "\n";
      $new_template .= \rtrim((string) \file_get_contents($path), "\n") . "\n";
      $new_template .= '{% elseif variant == "';
    }
    // Check if at least one variant had a custom template.
    if (\strlen($new_template) > \strlen('{% if variant == "')) {
      $new_template = \substr($new_template, 0, \strlen('{% elseif variant == "') * -1);
      $new_template .= '{% else %}' . "\n";
      $new_template .= \rtrim((string) \file_get_contents($target), "\n") . "\n";
      $new_template .= '{% endif %}' . "\n";
      \file_put_contents($target, $new_template);
    }
  }

}
