<?php

declare(strict_types = 1);

namespace Drupal\oe_bootstrap_theme_helper\Template\Loader;

use Drupal\Core\Extension\Extension;
use Drupal\Core\Theme\ThemeInitializationInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use OpenEuropa\Twig\Loader\EuropaComponentLibraryLoader;

/**
 * Load BCL components Twig templates.
 *
 * The Bootstrap Component Library (BCL) components are usually placed under
 * `assets/bcl` directory, relative to theme. But sub-themes are able to
 * override this location by specifying a different sub-path in the theme's
 * .info.yml file. For instance:
 * @code
 * openeuropa:
 *   bootstrap_component_library:
 *     components_location: components/templates
 * @endcode
 * Each component directory should be prefixed with `bcl-`. The component Twig
 * template file name should be prefixed with `bcl-` as well. For instance, the
 * accordion BCL component path should be:
 * @code
 * assets/bcl/bcl-accordion/bcl-accordion.html.twig
 * @endcode
 * In the above example, BCL components are located under `assets/bcl`, relative
 * to theme directory.
 */
class BclComponentLibraryLoader extends EuropaComponentLibraryLoader {

  /**
   * Associative array of BCL components paths.
   *
   * The key is the theme name and the value is the path to the BCL components
   * location for each theme.
   *
   * @var string[]
   */
  protected $bclPaths = [];

  /**
   * Constructs a new Twig loader instance.
   *
   * @param string $root
   *   The application root directory.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   *   The theme manager service.
   * @param \Drupal\Core\Theme\ThemeInitializationInterface $theme_initialization
   *   The theme initialization service.
   */
  public function __construct(string $root, ThemeManagerInterface $theme_manager, ThemeInitializationInterface $theme_initialization) {
    // Make sure the theme exists before getting its path. This is necessary
    // when the theme is not yet installed while the "oe_bootstrap_theme_helper"
    // module is enabled. Typically, this happens on site install.
    if ($theme = $theme_manager->getActiveTheme()) {
      $this->addBclComponentsPath($theme->getExtension());
      $base_theme_extensions = $theme_initialization->getActiveThemeByName($theme->getName())->getBaseThemeExtensions();
      foreach ($base_theme_extensions as $base_theme_extension) {
        $this->addBclComponentsPath($base_theme_extension);
      }
    }

    parent::__construct(['oe-bcl'], $this->bclPaths, $root, 'bcl-', 'bcl-');
  }

  /**
   * Appends a theme extension BCL components path to the BCL path list.
   *
   * @param \Drupal\Core\Extension\Extension $theme_extension
   *   The theme extension object.
   */
  protected function addBclComponentsPath(Extension $theme_extension): void {
    // Defaults to `assets/bcl`.
    $theme_relative_path = $theme_extension->info['openeuropa']['bootstrap_component_library']['components_location'] ?? 'assets/bcl';
    $path = "{$theme_extension->getPath()}/$theme_relative_path";
    if (is_dir($path)) {
      $this->bclPaths[$theme_extension->getName()] = $path;
    }
  }

}
