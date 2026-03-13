<?php

declare(strict_types=1);

namespace Drupal\oe_bootstrap_theme;

use Drupal\Component\Utility\DeprecationHelper;

/**
 * Handles Drupal core compatibility behaviors.
 */
final class DrupalCompatibility {

  /**
   * Gets a theme setting across supported Drupal core versions.
   *
   * @param string $setting
   *   The setting name.
   *
   * @return mixed
   *   The setting value.
   */
  public static function themeGetSetting(string $setting): mixed {
    return DeprecationHelper::backwardsCompatibleCall(
      \Drupal::VERSION,
      '11.3.0',
      fn () => \Drupal::service('Drupal\Core\Extension\ThemeSettingsProvider')->getSetting($setting),
      fn () => theme_get_setting($setting),
    );
  }

  /**
   * Resets the theme settings cache across supported Drupal core versions.
   *
   * In Drupal >= 11.3, ThemeSettingsProvider uses an in-memory cache that must
   * be cleared separately from the config factory static cache.
   */
  public static function resetThemeSettingsCache(): void {
    DeprecationHelper::backwardsCompatibleCall(
      \Drupal::VERSION,
      '11.3.0',
      fn () => \Drupal::service('cache.memory')->deleteAll(),
      fn () => drupal_static_reset('theme_get_setting'),
    );
    \Drupal::configFactory()->clearStaticCache();
  }

}
