<?php

declare(strict_types=1);

namespace Drupal\oe_bootstrap_theme_helper\Value;

use Twig\Error\RuntimeError;

/**
 * Contains a sentinel value to indicate the start of named-only parameters.
 *
 * This protects from passing parameters in the wrong order.
 *
 * Note:
 * The sentinel value could have been a single-case enum, but that does not work
 * in the current version of Twig.
 */
class NamedParametersOnly {

  /**
   * Indicates the beginning of named-only parameters.
   *
   * This is meant to be used as a default value for a parameter.
   * That parameter itself is not meant to be passed ever, while any subsequent
   * parameter should only be passed by name.
   */
  const SENTINEL = '*#NAMED_PARAMETERS_ONLY#*';

  /**
   * Asserts that an argument is exactly the sentinel value.
   *
   * @param mixed $value
   *   An argument that was passed to a different function.
   *
   * @throws \Twig\Error\RuntimeError
   *   An exception that will be caught in the twig engine.
   */
  public static function checkForTwig(mixed $value): void {
    if ($value !== self::SENTINEL) {
      throw new RuntimeError(sprintf(
        "Expected only named parameters from here on, found %s.",
        get_debug_type($value),
      ));
    }
  }

}
