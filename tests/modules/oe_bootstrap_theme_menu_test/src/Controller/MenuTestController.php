<?php

declare(strict_types=1);

namespace Drupal\oe_bootstrap_theme_menu_test\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller routines for menu_test routes.
 */
class MenuTestController extends ControllerBase {

  /**
   * Some known placeholder content which can be used for testing.
   *
   * @return string
   *   A string that can be used for comparison.
   */
  public function callback() {
    return ['#markup' => 'This is the menuTestCallback content.'];
  }

}
