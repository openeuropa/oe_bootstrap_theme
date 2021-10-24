<?php

declare(strict_types = 1);

namespace Drupal\oe_bootstrap_theme_helper\Template\Loader;

use Drupal\Core\Extension\ThemeHandlerInterface;
use OpenEuropa\Twig\Loader\EuropaComponentLibraryLoader;

/**
 * Load BCL components Twig templates.
 */
class BclComponentLibraryLoader extends EuropaComponentLibraryLoader {

  /**
   * Constructs a new Twig loader instance.
   *
   * @param string $root
   *   The application root directory.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler service.
   */
  public function __construct(string $root, ThemeHandlerInterface $theme_handler) {
    // Make sure the theme exists before getting its path. This is necessary
    // when the theme is not yet installed while the "oe_bootstrap_theme_helper"
    // module is enabled. Typically, this happens on site install.
    $bcl_path = '';
    if ($theme_handler->themeExists('oe_bootstrap_theme')) {
      $theme_path = $theme_handler->getTheme('oe_bootstrap_theme')->getPath();
      $bcl_path = $theme_path . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'bcl';
    }

    parent::__construct(['oe-bcl'], $bcl_path, $root, 'bcl-');
  }


}
