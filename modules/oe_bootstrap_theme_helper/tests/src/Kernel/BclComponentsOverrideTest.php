<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme_helper\Kernel;

use Drupal\Core\Serialization\Yaml;
use Drupal\Core\Site\Settings;
use Drupal\KernelTests\KernelTestBase;

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

    // Replace some original BCL components just to make it easy to test.
    $path = __DIR__ . '/../../../../../assets/bcl';
    file_put_contents("$path/bcl-alert/bcl-alert.html.twig", 'Alert: oe_bootstrap_theme version');
    file_put_contents("$path/bcl-badge/bcl-badge.html.twig", 'Badge: oe_bootstrap_theme version');
    file_put_contents("$path/bcl-blockquote/bcl-blockquote.html.twig", 'Blockquote: oe_bootstrap_theme version');
    file_put_contents("$path/bcl-card/bcl-card.html.twig", 'Card: oe_bootstrap_theme version');
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
    $cases = Yaml::decode(file_get_contents(__DIR__ . '/../../fixtures/bcl_components_override_test_cases.yml'));
    array_walk($cases, function (array &$case, string $theme): void {
      $case = [$theme, $case];
    });
    return $cases;
  }

}
