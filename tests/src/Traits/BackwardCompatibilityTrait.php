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
