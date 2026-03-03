<?php

declare(strict_types=1);

namespace Drupal\oe_bootstrap_theme;

/**
 * Handles backward compatibility.
 */
final class BackwardCompatibility {

  /**
   * The prefix for backward-compatible settings.
   */
  public const PREFIX = 'backward_compatibility.';

  /**
   * The theme where backward compatibility settings are stored.
   */
  private const THEME = 'oe_bootstrap_theme';

  /**
   * Returns the value of a backward compatibility setting.
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
    return (bool) (DrupalCompatibility::themeGetSetting(self::PREFIX . $name, self::THEME) ?? TRUE);
  }

}
