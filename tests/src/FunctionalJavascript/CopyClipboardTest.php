<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests the copy to clipboard javascript behaviour.
 */
class CopyClipboardTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'oe_bootstrap_theme';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'oe_bootstrap_theme_helper',
    'system',
    'ui_patterns',
    'ui_patterns_settings',
    'ui_patterns_library',
  ];

  /**
   * Tests the copy to clipboard behavior in the copyright overlay.
   */
  public function testCopyToClipboard(): void {
    $assert_session = $this->assertSession();

    $admin_user = $this->drupalCreateUser([], NULL, TRUE);
    $this->drupalLogin($admin_user);

    $this->drupalGet('/patterns/copyright_overlay');

    $assert_session->elementExists('css', '.copyright-trigger')->click();
    $assert_session->waitForElementVisible('css', '.modal.show');

    // Mock the clipboard API.
    $this->getSession()->executeScript(<<<JS
      navigator.clipboard = {
        writeText: function(text) {
          // Store the copied text in a variable.
          window.copiedText = text;
          return Promise.resolve();
        }
      };
    JS);

    $assert_session->elementExists('css', '[data-copy-target]')->click();

    $expectedText = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse vel mauris vitae ipsum blandit condimentum ut eget quam.';
    $actualText = $this->getSession()->evaluateScript('return window.copiedText;');

    $this->assertEquals($expectedText, $actualText);
  }

}
