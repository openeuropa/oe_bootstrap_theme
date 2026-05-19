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
      'assets/logos/ec/logo-ec--en.svg',
      'assets/logos/ec/logo-ec--fr.svg',
      'assets/logos/eu/logo-eu--en.svg',
      'assets/logos/eu/mobile/logo-eu--en.svg',
    ];

    $missing_files = array_values(array_filter(
      $expected_files,
      static fn (string $file): bool => !file_exists($theme_root . '/' . $file)
    ));

    $this->assertSame([], $missing_files, 'Missing canary files.');
  }

}
