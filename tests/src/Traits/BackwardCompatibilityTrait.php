<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Traits;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\oe_bootstrap_theme\BackwardCompatibility;

/**
 * Contains method that handle backward compatibility.
 */
trait BackwardCompatibilityTrait {

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
    $key = BackwardCompatibility::PREFIX . $name;
    $load_theme_config = fn () => $this->container->get(ConfigFactoryInterface::class)->getEditable('oe_bootstrap_theme.settings');
    $theme_config = $load_theme_config();
    $original_value = $theme_config->get($key);
    $theme_config->set($key, $value)->save();
    drupal_static_reset();
    return function () use ($key, $original_value, $load_theme_config): void {
      $theme_config = $load_theme_config();
      $theme_config->set($key, $original_value)->save();
      drupal_static_reset();
    };
  }

}
