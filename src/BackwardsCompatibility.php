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
   *   The setting name, without the "oebt_bc" prefix.
   *
   * @return bool
   *   The setting value. Settings without a defined value are returned as TRUE,
   *   to maintain the backward compatibility.
   */
  public static function getSetting(string $name): bool {
    return theme_get_setting('oebt_bc.' . $name) ?? TRUE;
  }

}
