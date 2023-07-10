<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\Traits;

use Drupal\oe_bootstrap_theme\BackwardCompatibility;

/**
 * Contains method that handle backwards compatibility.
 */
trait BackwardCompatibilityTrait {

  /**
   * Sets a backward compatibility setting.
   *
   * @param string $name
   *   The setting name.
   * @param bool $value
   *   The value.
   */
  protected function setBackwardCompatibilitySetting(string $name, bool $value): void {
    $theme_config = $this->container->get('config.factory')->getEditable('oe_bootstrap_theme.settings');
    $theme_config->set(BackwardCompatibility::PREFIX . $name, $value)->save();
    drupal_static_reset();
  }

}
