<?php

// phpcs:ignoreFile -- Until https://www.drupal.org/project/coder/issues/3322615#comment-14945035

declare(strict_types = 1);

namespace Drupal\oe_bootstrap_theme_helper\Commands;

use Consolidation\AnnotatedCommand\CommandData;
use Consolidation\AnnotatedCommand\Hooks\HookManager;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Task runner commands for sub-theme generation.
 */
class OeBootstrapThemeCommands extends DrushCommands {

  const CREATE_SUBTHEME_OPTIONS = [
    'name' => InputOption::VALUE_REQUIRED,
    'description' => InputOption::VALUE_REQUIRED,
    'machine-name' => InputOption::VALUE_REQUIRED,
    'delete' => FALSE,
  ];

  /**
   * Provides the oe_bootstrap_theme:create-subtheme Drush command.
   *
   * @param string $destination
   *   The destination argument. See method attributes.
   * @param array $options
   *   The Drush command options. See method attributes.
   */
  #[CLI\Command(name: 'oe_bootstrap_theme:create-subtheme',)]
  #[CLI\Help(description: 'Create a sub-theme based on OE Bootstrap Theme',)]
  #[CLI\Argument(name: 'destination', description: 'Destination path')]
  #[CLI\Option(name: 'name', description: 'The human-readable name for the new sub-theme.')]
  #[CLI\Option(name: 'description', description: 'The sub-theme description to be used in the *.info.yml and in package.json files.')]
  #[CLI\Option(name: 'machine-name', description: 'The machine-readable name for the new sub-theme. If not provided, the machine name will be the last part of the destination path.')]
  #[CLI\Option(name: 'delete', description: 'Delete the destination directory, if it already exists.')]
  public function createSubtheme(string $destination, array $options = self::CREATE_SUBTHEME_OPTIONS): void {
    $fileSystem = new Filesystem();

    // In this proof-of-concept version, only the default kit is supported.
    $kit_name = 'default';
    $kit_path = dirname(__DIR__, 4) . '/kits/' . $kit_name;

    $machine_name = $options['machine-name'] ?? basename($destination);

    if ($fileSystem->exists($destination)) {
      // Destination directory exists.
      if (Finder::create()->in($destination)->hasResults()) {
        // Destination directory is not empty.
        if (!$options['delete']) {
          throw new \Exception("Destination directory not empty: $destination");
        }
        $fileSystem->remove($destination);
      }
    }

    // Copy / mirror the kit directory.
    $fileSystem->mirror($kit_path, $destination);

    if (!$fileSystem->exists($destination)) {
      throw new \Exception("Directory was not created: $destination.");
    }

    $replacements = [
      'OE_BOOTSTRAP_THEME_SUBTHEME_MACHINE_NAME' => $machine_name,
      'OE_BOOTSTRAP_THEME_SUBTHEME_NAME' => $options['name'],
      'OE_BOOTSTRAP_THEME_SUBTHEME_DESCRIPTION' => $options['description'],
    ];

    $rename_pattern = '@^' . preg_quote($kit_name, '@') . '((?:\.\w+)+)$@';

    // Process all files that occur in both the origin and destination dir.
    foreach (Finder::create()
      ->files()
      ->in($destination) as $file
    ) {
      // Replace file contents, if applicable.
      $contents_orig = $file->getContents();
      $contents_new = strtr($contents_orig, $replacements);
      if ($contents_orig !== $contents_new) {
        $fileSystem->dumpFile($file->getPathname(), $contents_new);
      }

      // Rename file, if applicable.
      if (preg_match($rename_pattern, $basename = $file->getBasename(), $m)) {
        $ext = $m[1];
        if ($ext === '.info.hidden.yml') {
          $ext = '.info.yml';
        }
        $fileSystem->rename(
          $file->getPathname(),
          $file->getPath() . '/' . $machine_name . $ext);
      }
    }
  }

  /**
   * Validates the oe_bootstrap_theme:create-subtheme command.
   */
  #[CLI\Hook(type: HookManager::ARGUMENT_VALIDATOR, target: 'oe_bootstrap_theme:create-subtheme')]
  public function validate(CommandData $commandData): void {
    $missingOptions = [];
    foreach (['name', 'description'] as $option) {
      if (empty($commandData->options()[$option])) {
        $missingOptions[] = "--$option";
      }
    }
    if ($missingOptions) {
      throw new \Exception(dt('Missing option(s): {options}', [
        'options' => implode(', ', $missingOptions),
      ]));
    }
  }

}
