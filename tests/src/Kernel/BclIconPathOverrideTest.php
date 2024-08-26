<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel;

use Drupal\Core\Site\Settings;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the bcl_icon_path theme override functionality.
 */
class BclIconPathOverrideTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
  ];

  /**
   * Set up the test environment.
   */
  protected function setUp(): void {
    parent::setUp();

    // Replicate 'file_scan_ignore_directories' from settings.php.
    $settings = Settings::getAll();
    $settings['file_scan_ignore_directories'] = [
      'node_modules',
      'bower_components',
      'vendor',
      'build',
    ];
    new Settings($settings);
  }

  /**
   * Tests that the bcl_icon_path is correctly handled in the theme.
   *
   * @param string $theme
   *   The theme to set as default.
   * @param string $expected_icon_path
   *   The expected bcl_icon_path value in the theme.
   *
   * @dataProvider bclIconPathTestCasesProvider
   */
  public function testBclIconPath(string $theme, string $expected_icon_path): void {
    // Install the theme. Subsequently, this installs also the base themes.
    $this->container->get('theme_installer')->install([$theme]);
    $this->config('system.theme')->set('default', $theme)->save();

    // In order to call the preprocess function we need to initialize the theme.
    // Retrieving the active theme will do a full initialisation.
    \Drupal::theme()->getActiveTheme();

    // Create a renderable array to simulate the theme preprocess.
    $variables = [];
    oe_bootstrap_theme_preprocess($variables);

    // Extract the path set in the preprocess function.
    $actual_icon_path = $variables['bcl_icon_path'];

    // Check if the paths match.
    $this->assertEquals($expected_icon_path, $actual_icon_path);
  }

  /**
   * Provides test cases for ::testBclIconPath().
   *
   * @return array
   *   A list of test cases.
   */
  public function bclIconPathTestCasesProvider(): array {
    return [
      'subtheme theme with path provided' => [
        'oe_bootstrap_theme_test_subtheme1',
        '/themes/custom/oe_bootstrap_theme/modules/oe_bootstrap_theme_helper/tests/themes/oe_bootstrap_theme_test_subtheme1/assets/icons/bcl-default-icons-test.svg',
      ],
      'subtheme theme with subtheme as base theme that has path provided' => [
        'oe_bootstrap_theme_test_subtheme2',
        '/themes/custom/oe_bootstrap_theme/modules/oe_bootstrap_theme_helper/tests/themes/oe_bootstrap_theme_test_subtheme1/assets/icons/bcl-default-icons-test.svg',
      ],
      'subtheme theme with base theme with default path' => [
        'oe_bootstrap_theme_test_subtheme3',
        '/themes/custom/oe_bootstrap_theme/assets/icons/bcl-default-icons.svg',
      ],
      'base theme with default path' => [
        'oe_bootstrap_theme',
        '/themes/custom/oe_bootstrap_theme/assets/icons/bcl-default-icons.svg',
      ],
    ];
  }

}
