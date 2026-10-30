<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Traits;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\oe_bootstrap_theme\BackwardCompatibility;

/**
 * Contains methods that handle backward compatibility.
 */
trait BackwardCompatibilityTrait {

  /**
   * Invokes a callback with backwards compatibility settings.
   *
   * @param array<string, mixed> $settings
   *   The setting values by name.
   * @param callable(): T $callback
   *   A callback to invoke while the settings are active.
   *
   * @return T
   *   The return value from the callback.
   *
   * @template T
   */
  protected function withBackwardCompatibilitySettings(array $settings, callable $callback): mixed {
    $restore_callbacks = [];
    try {
      foreach ($settings as $name => $value) {
        $restore_callbacks[] = $this->setBackwardCompatibilitySetting($name, $value);
      }
      return $callback();
    }
    finally {
      foreach (array_reverse($restore_callbacks) as $callback) {
        $callback();
      }
    }
  }

  /**
   * Sets a backward compatibility setting.
   *
   * @param string $name
   *   The setting name.
   * @param bool $value
   *   The value.
   *
   * @return \Closure(): void
   *   A callback to restore the original value.
   */
  protected function setBackwardCompatibilitySetting(string $name, bool $value): \Closure {
    $theme_config = $this->container->get(ConfigFactoryInterface::class)->getEditable('oe_bootstrap_theme.settings');
    $key = BackwardCompatibility::PREFIX . $name;
    $original_value = $theme_config->get($key);
    $theme_config->set(BackwardCompatibility::PREFIX . $name, $value)->save();
    drupal_static_reset();
    return function () use ($theme_config, $key, $original_value): void {
      $theme_config->set($key, $original_value);
      drupal_static_reset();
    };
  }

}
