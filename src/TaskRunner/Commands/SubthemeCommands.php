<?php

declare(strict_types = 1);

namespace Drupal\oe_bootstrap_theme\TaskRunner\Commands;

use Drupal\oe_bootstrap_theme\CommandUtil;
use Symfony\Component\Finder\Finder;
use OpenEuropa\TaskRunner\Commands\AbstractCommands;
use OpenEuropa\TaskRunner\Contract\FilesystemAwareInterface;
use OpenEuropa\TaskRunner\Traits\FilesystemAwareTrait;
use Symfony\Component\Console\Input\InputOption;

/**
 * Task runner commands for sub-theme generation.
 */
class SubthemeCommands extends AbstractCommands implements FilesystemAwareInterface {

  use FilesystemAwareTrait;

  const CREATE_SUBTHEME_OPTIONS = [
    'name' => InputOption::VALUE_REQUIRED,
    'description' => InputOption::VALUE_REQUIRED,
    'machine-name' => InputOption::VALUE_OPTIONAL,
    'delete' => FALSE,
  ];

  /**
   * Runs an npm command in a given directory.
   *
   * @param string $destination
   *   Destination path.
   * @param array $options
   *   Destination path for the new sub-theme, with the name as last fragment.
   *
   * @command oe_bootstrap_theme:create-subtheme
   *
   * @option name
   *   Human name for the new sub-theme.
   * @option description
   *   Human name for the new sub-theme.
   *   This will be used in *.info.yml and in package.json.
   * @option machine-name
   *   (optional) Machine name for the new sub-theme.
   *   If not provided, the machine name will be the last part of the
   *   destination path.
   * @option delete
   *   Delete the destination directory, if it already exists.
   *
   * @throws \Exception
   *   The task could not be constructed with the given parameters.
   */
  public function createSubtheme(string $destination, array $options = self::CREATE_SUBTHEME_OPTIONS) {
    // For some reason, task-runner does not properly verify the options.
    CommandUtil::validateOptions($options, self::CREATE_SUBTHEME_OPTIONS);

    // In this proof-of-concept version, only the default kit is supported.
    $kit_name = 'default';
    $kit_path = dirname(__DIR__, 3) . '/kits/' . $kit_name;

    $machine_name = $options['machine-name'] ?? basename($destination);

    if ($this->filesystem->exists($destination)) {
      // Destination directory exists.
      if (Finder::create()->in($destination)->hasResults()) {
        // Destination directory is not empty.
        if (!$options['delete']) {
          throw new \Exception("Destination directory not empty: $destination");
        }
        $this->filesystem->remove($destination);
      }
    }

    // Copy / mirror the kit directory.
    $this->filesystem->mirror($kit_path, $destination);

    if (!$this->filesystem->exists($destination)) {
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
        $this->filesystem->dumpFile($file->getPathname(), $contents_new);
      }

      // Rename file, if applicable.
      if (preg_match($rename_pattern, $basename = $file->getBasename(), $m)) {
        $ext = $m[1];
        if ($ext === '.info.hidden.yml') {
          $ext = '.info.yml';
        }
        $this->filesystem->rename(
          $file->getPathname(),
          $file->getPath() . '/' . $machine_name . $ext);
      }
    }
  }

}
