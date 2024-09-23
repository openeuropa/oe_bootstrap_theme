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
   * Tests the copy to clipboard behavior in the copyright overlay.
   */
  public function testCopyToClipboard(): void {
    $this->drupalGet('/patterns/copyright_overlay');

    // Open the modal by clicking the trigger.
    $this->assertSession()->elementExists('css', '.copyright-trigger')->click();
    $this->assertSession()->waitForElementVisible('css', '.modal.show');

    // Ensure the copy button exists and simulate click.
    $this->assertSession()->elementExists('css', '[data-copy-target=".copyright-content"]');
    $this->getSession()->getPage()->clickButton('[data-copy-target=".copyright-content"]');

    // Get the clipboard text via JavaScript.
    $expectedText = '© Lorem ipsum amet <a href="#">John Doe</a> on <a href="#">Doe Images</a>';
    $actualText = $this->getClipboardText();

    // Assert the copied text matches expected text.
    $this->assertEquals($expectedText, $actualText);
  }

  /**
   * Helper method to retrieve clipboard text using JavaScript.
   */
  protected function getClipboardText(): string {
    return $this->getSession()->evaluateScript('return navigator.clipboard.readText();');
  }

}
