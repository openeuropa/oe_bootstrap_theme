<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme_helper\Kernel;

use Drupal\Core\Theme\ThemeManagerInterface;
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
    'theme_test',
  ];

  /**
   * The theme manager service.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected ThemeManagerInterface $themeManager;

  /**
   * Set up the test environment.
   */
  protected function setUp(): void {
    parent::setUp();

    // Set the theme manager service.
    $this->themeManager = $this->container->get('theme.manager');

    // Install the required themes and enable your custom theme.
    $this->installThemes(['oe_bootstrap_theme', 'oe_bootstrap_theme_test_subtheme1']);
    $this->themeManager->setActiveTheme($this->themeManager->getTheme('oe_bootstrap_theme_test_subtheme1'));
  }

  /**
   * Tests that the bcl_icon_path is correctly overridden in the subtheme.
   */
  public function testBclIconPathOverride(): void {
    // Retrieve the active theme.
    $activeTheme = $this->themeManager->getActiveTheme();

    // Build the expected path.
    $expectedIconPath = 'assets/icons/bcl-default-icons-test.svg';

    // Access the actual path from the theme info.
    $actualIconPath = $activeTheme->getExtension()->info['bcl_icon_path'] ?? 'assets/icons/bcl-default-icons.svg';

    // Check if the paths match.
    $this->assertEquals($expectedIconPath, $actualIconPath, 'The bcl_icon_path is correctly overridden in the subtheme.');
  }

  /**
   * Tests the fallback when the bcl_icon_path is not set in the subtheme.
   */
  public function testBclIconPathFallback(): void {
    // Switch back to the base theme.
    $this->themeManager->setActiveTheme($this->themeManager->getTheme('oe_bootstrap_theme'));

    // Build the expected path for the base theme.
    $expectedBaseIconPath = 'assets/icons/bcl-default-icons.svg';

    // Access the actual path from the base theme info.
    $actualBaseIconPath = $this->themeManager->getActiveTheme()->getExtension()->info['bcl_icon_path'] ?? 'assets/icons/bcl-default-icons.svg';

    // Check if the fallback path matches.
    $this->assertEquals($expectedBaseIconPath, $actualBaseIconPath, 'The bcl_icon_path falls back to the base theme when no override is provided in the subtheme.');
  }

}
