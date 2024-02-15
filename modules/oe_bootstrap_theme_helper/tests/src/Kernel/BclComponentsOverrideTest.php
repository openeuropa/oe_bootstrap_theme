<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme_helper\Kernel;

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
   *   The theme to set as default.
   * @param array $templates
   *   Associative array of templates to be tested in $theme, having the
   *   template name as keys and the expected path parts as values.
   *   The path parts consist of the theme name and the relative path of the
   *   expected template to be loaded inside the theme folder.
   *
   * @dataProvider bclComponentsOverridingTestCasesProvider
   */
  public function testBclComponentsOverriding(string $theme, array $templates): void {
    // Install the theme. Subsequently, this installs also the base themes.
    $this->container->get('theme_installer')->install([$theme]);
    $this->config('system.theme')->set('default', $theme)->save();

    $twig_environment = $this->container->get('twig');
    $theme_list_service = $this->container->get('extension.list.theme');

    foreach ($templates as $template_name => $path_parts) {
      // Generate the path to the expected theme and template.
      $expected_path = $theme_list_service->getPath($path_parts[0]) . $path_parts[1];
      // Twig resolves symlinks, so in order to match the path we need to do the
      // same. We do so only if the file exists, otherwise realpath() will
      // return false and the test failure will be less clear.
      if (file_exists($expected_path)) {
        $expected_path = realpath($expected_path);
      }

      $template = $twig_environment->load("@oe-bcl/$template_name");
      $this->assertSame($expected_path, $template->getSourceContext()->getPath());
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
        'oe_bootstrap_theme',
        [
          'button' => [
            'oe_bootstrap_theme',
            '/assets/bcl/bcl-button/bcl-button.html.twig',
          ],
          'badge' => [
            'oe_bootstrap_theme',
            '/assets/bcl/bcl-badge/bcl-badge.html.twig',
          ],
          'link' => [
            'oe_bootstrap_theme',
            '/assets/bcl/bcl-link/bcl-link.html.twig',
          ],
          'icon' => [
            'oe_bootstrap_theme',
            '/assets/bcl/bcl-icon/bcl-icon.html.twig',
          ],
        ],
      ],
      'oe_bootstrap_theme_test_subtheme1 as active theme' => [
        'oe_bootstrap_theme_test_subtheme1',
        [
          'button' => [
            'oe_bootstrap_theme',
            '/assets/bcl/bcl-button/bcl-button.html.twig',
          ],
          'badge' => [
            'oe_bootstrap_theme_test_subtheme1',
            '/components/templates/bcl-badge/bcl-badge.html.twig',
          ],
          'link' => [
            'oe_bootstrap_theme_test_subtheme1',
            '/components/templates/bcl-link/bcl-link.html.twig',
          ],
          'icon' => [
            'oe_bootstrap_theme',
            '/assets/bcl/bcl-icon/bcl-icon.html.twig',
          ],
        ],
      ],
      'oe_bootstrap_theme_test_subtheme2 as active theme' => [
        'oe_bootstrap_theme_test_subtheme2',
        [
          'button' => [
            'oe_bootstrap_theme_test_subtheme2',
            '/assets/bcl/bcl-button/bcl-button.html.twig',
          ],
          'badge' => [
            'oe_bootstrap_theme_test_subtheme1',
            '/components/templates/bcl-badge/bcl-badge.html.twig',
          ],
          'link' => [
            'oe_bootstrap_theme_test_subtheme2',
            '/assets/bcl/bcl-link/bcl-link.html.twig',
          ],
          'icon' => [
            'oe_bootstrap_theme',
            '/assets/bcl/bcl-icon/bcl-icon.html.twig',
          ],
        ],
      ],
    ];
  }

}
