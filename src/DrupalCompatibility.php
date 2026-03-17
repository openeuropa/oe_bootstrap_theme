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
   * Resets the theme settings memory cache across supported Drupal versions.
   *
   * This is needed in functional tests, when a separate request made changes to
   * configuration, but the old version is still in the memory cache of the
   * process where that test is running.
   *
   * In Drupal < 11.3, this memory cache was using the drupal_static()
   * mechanism. In Drupal >= 11.3, the new memory cache service is used.
   *
   * See https://www.drupal.org/node/3035289.
   */
  public static function resetThemeSettingsCache(): void {
    DeprecationHelper::backwardsCompatibleCall(
      \Drupal::VERSION,
      '11.3.0',
      fn () => \Drupal::service('cache_tags.invalidator')->invalidateTags(['config:system.theme.global']),
      fn () => drupal_static_reset('theme_get_setting'),
    );
  }

}
