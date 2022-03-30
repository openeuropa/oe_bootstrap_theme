<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests Slim Select.
 */
class SlimSelectTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'oe_bootstrap_theme_helper',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'oe_bootstrap_theme';

  /**
   * Test Slim Select toggling.
   */
  public function testSlimSelectToggle(): void {
    $assert = $this->assertSession();
    $this->drupalLogin($this->createUser([], NULL, TRUE));

    // Navigate to a page having a select.
    $this->drupalGet('/admin/config/development/performance');
    $assert->responseContains('oe_bootstrap_theme/assets/js/slimselect.min.js');

    $this->drupalGet('/admin/appearance/settings/oe_bootstrap_theme');
    $page = $this->getSession()->getPage();

    $page->uncheckField('Enable Slim Select on all select form elements');
    $page->pressButton('Save configuration');

    $this->drupalGet('/admin/config/development/performance');
    $assert->responseNotContains('oe_bootstrap_theme/assets/js/slimselect.min.js');
  }

}
