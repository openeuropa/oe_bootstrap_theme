<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests the copy to clipboard functionality.
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

    // Create and log in an admin user with full permissions.
    $admin_user = $this->drupalCreateUser([], NULL, TRUE);
    $this->drupalLogin($admin_user);

    // Navigate to the copyright overlay page.
    $this->drupalGet('/patterns/copyright_overlay');

    // Open the modal by clicking the trigger.
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

    // Ensure the copy button exists and simulate a click.
    $assert_session->elementExists('css', '[data-copy-target=".copyright-content"]')->click();

    // Now get the text from the mocked clipboard.
    $expectedText = '© Lorem ipsum amet John Doe on Doe Images';
    $actualText = $this->getSession()->evaluateScript('return window.copiedText;');

    // Assert the copied text matches the expected text.
    $this->assertEquals($expectedText, $actualText);
  }

}
