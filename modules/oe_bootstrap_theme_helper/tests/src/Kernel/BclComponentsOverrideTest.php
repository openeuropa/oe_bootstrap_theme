<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme_helper\Kernel;

use Drupal\Core\Serialization\Yaml;
use Drupal\Core\Site\Settings;
use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Tests that OE Boostrap Theme BCL components are overridable by sub-themes.
 *
 * Specifically, this test checks that a Bootstrap Component Library (BCL)
 * component, available in OE Bootstrap Theme, can be overridden by a sub-theme
 * similar component.
 *
 * @group oe_bootstrap_theme
 */
class BclComponentsOverrideTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'oe_bootstrap_theme_helper',
    'system',
    'ui_patterns',
    'ui_patterns_library',
  ];

  /**
   * {@inheritdoc}
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
   * Tests that BCL components are overridable in sub-themes.
   *
   * @param string $theme
   *   The theme being installed.
   * @param array $patterns
   *   Associative array of patterns to be tested in $theme, having the pattern
   *   ID as keys and the expected pattern output as values.
   *
   * @dataProvider bclComponentsOverridingTestCasesProvider
   */
  public function testBclComponentsOverriding(string $theme, array $patterns): void {
    // Install the theme. Subsequently, this installs also the base themes.
    $this->container->get('theme_installer')->install([$theme]);
    $this->config('system.theme')->set('default', $theme)->save();

    foreach ($patterns as $pattern => $expected_markup) {
      $render_array = [
        '#type' => 'pattern',
        '#id' => $pattern,
      ];
      $actual_markup = trim((string) $this->container->get('renderer')->renderRoot($render_array));
      $this->assertSame($expected_markup, $actual_markup);
    }
  }

  /**
   * Provides test cases for ::testBclComponentsOverriding().
   *
   * @return array
   *   A list of test cases.
   *
   * @see self::testBclComponentsOverriding()
   */
  public function bclComponentsOverridingTestCasesProvider(): array {
    return [
      'oe_bootstrap_theme as active theme' => [
        'oe_bootstrap_theme_test_parent',
        [
          'button' => 'Button: oe_bootstrap_theme_test_parent version',
          'badge' => 'Badge: oe_bootstrap_theme_test_parent version',
          'link' => 'Link: oe_bootstrap_theme_test_parent version',
          'icon' => 'Icon: oe_bootstrap_theme_test_parent version',
        ],
      ],
      'oe_bootstrap_theme_test_subtheme1 as active theme' => [
        'oe_bootstrap_theme_test_subtheme1',
        [
          'button' => 'Button: oe_bootstrap_theme_test_parent version',
          'badge' => 'Badge: oe_bootstrap_theme_test_subtheme1 version',
          'link' => 'Link: oe_bootstrap_theme_test_subtheme1 version',
          'icon' => 'Icon: oe_bootstrap_theme_test_parent version',
        ],
      ],
      'oe_bootstrap_theme_test_subtheme2 as active theme' => [
        'oe_bootstrap_theme_test_subtheme2',
        [
          'button' => 'Button: oe_bootstrap_theme_test_subtheme2 version',
          'badge' => 'Badge: oe_bootstrap_theme_test_subtheme1 version',
          'link' => 'Link: oe_bootstrap_theme_test_subtheme2 version',
          'icon' => 'Icon: oe_bootstrap_theme_test_parent version',
        ],
      ],
    ];
  }

}
