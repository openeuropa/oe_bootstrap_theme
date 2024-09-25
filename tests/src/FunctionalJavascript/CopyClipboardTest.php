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
    $this->getSession()->executeScript('
      navigator.clipboard = {
        writeText: function(text) {
          window.copiedText = text; // Store the copied text in a variable
          return Promise.resolve();
        }
      };
    ');

    $assert_session->elementExists('css', '[data-copy-target=".copyright-content"]')->click();

    $expectedText = '© Lorem ipsum amet John Doe on Doe Images';
    $actualText = $this->getSession()->evaluateScript('return window.copiedText;');

    $this->assertEquals($expectedText, $actualText);
  }

}
