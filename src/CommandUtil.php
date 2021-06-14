<?php

namespace Drupal\oe_bootstrap_theme;

use Symfony\Component\Console\Input\InputOption;

/**
 * Utility class with static methods related to robo commands.
 *
 * (Both drush and task-runner are based on robo)
 */
class CommandUtil {

  /**
   * Verifies that command options are complete.
   *
   * For some reason, task-runner does not properly validate the required
   * options, so we are doing it manually here.
   *
   * @param array $options
   *   User-provided options.
   * @param array $expected
   *   Expected options.
   *
   * @throws \Exception
   *   Options are incomplete.
   */
  public static function validateOptions(array $options, array $expected) {
    foreach ($expected as $name => $spec) {
      if ($spec === InputOption::VALUE_REQUIRED) {
        if ($options[$name] === NULL) {
          // @todo Should this be a different exception class?
          throw new \Exception("Missing option --$name.");
        }
      }
    }
  }

}
