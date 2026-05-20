<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Unit;

use Drupal\Tests\UnitTestCase;

/**
 * Tests that generated theme assets are available in expected locations.
 */
class ThemeAssetsTest extends UnitTestCase {

  /**
   * Tests that key generated asset files exist.
   */
  public function testCanaryAssetFilesExist(): void {
    $theme_root = dirname(__DIR__, 3);
    $expected_files = [
      'assets/bcl/bcl-button/bcl-button.html.twig',
      'assets/css/oe_bootstrap_theme.style.min.css',
      'assets/logos/ec/logo-ec--en.svg',
      'assets/logos/ec/logo-ec--fr.svg',
      'assets/logos/eu/logo-eu--en.svg',
      'assets/logos/eu/mobile/logo-eu--en.svg',
      'node_modules/@openeuropa/bcl-theme-default/css/oe-bcl-default.min.css',
      'node_modules/@openeuropa/bcl-theme-default/logos/ec/positive/logo-ec--en.svg',
      'node_modules/@openeuropa/bcl-theme-default/logos/eu/standard-version/positive/logo-eu--en.svg',
      'node_modules/@openeuropa/bcl-theme-default/templates/bcl-button/button.html.twig',
    ];

    $missing_files = array_values(array_filter(
      $expected_files,
      static fn (string $file): bool => !file_exists($theme_root . '/' . $file)
    ));

    $this->assertSame([], $missing_files, 'Missing canary files.');
  }

}
