<?php

declare(strict_types = 1);

namespace Drupal\oe_bootstrap_theme;

/**
 * Handles backwards compatibility.
 */
class BackwardsCompatibility {

  /**
   * Returns the value of a backwards compatibility setting.
   *
   * @param string $name
   *   The setting name, without the "backward_compatibility" prefix.
   *
   * @return bool
   *   The setting value. Settings without a defined value are returned as TRUE,
   *   to maintain the backward compatibility.
   *
   * @SuppressWarnings(PHPMD.BooleanGetMethodName)
   */
  public static function getSetting(string $name): bool {
    return theme_get_setting('backward_compatibility.' . $name) ?? TRUE;
  }

}
