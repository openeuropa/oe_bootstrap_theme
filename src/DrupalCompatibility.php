<?php

declare(strict_types=1);

namespace Drupal\oe_bootstrap_theme;

use Drupal\Component\Utility\DeprecationHelper;
use Drupal\Core\Extension\ThemeSettingsProvider;

/**
 * Handles Drupal core compatibility behaviors.
 */
final class DrupalCompatibility {

  /**
   * Core version where ThemeSettingsProvider::getSetting() should be used.
   */
  private const THEME_SETTINGS_PROVIDER_VERSION = '11.3.0';

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
      self::THEME_SETTINGS_PROVIDER_VERSION,
      fn () => \Drupal::service(ThemeSettingsProvider::class)->getSetting($setting),
      fn () => theme_get_setting($setting),
    );
  }

}
