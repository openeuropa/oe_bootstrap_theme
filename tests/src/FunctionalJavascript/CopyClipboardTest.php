<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\FunctionalJavascript;

use Behat\Mink\Element\NodeElement;
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
    $this->drupalLogin($this->drupalCreateUser([], NULL, TRUE));
    $this->drupalGet('/admin/appearance/ui/patterns/media_container');

    $this->getSession()->executeScript(<<<'JS'
      navigator.clipboard = {
        writeText: function (text) {
          window.copiedText = text;
          return Promise.resolve();
        }
      };
    JS);

    $elements = $this->getSession()->getPage()->findAll('css', '.copyright-overlay');
    $this->assertNotEmpty($elements);

    $this->assertCopyrightCopiedToClipboard($elements[0], '© European Commission, 2024');
  }

  /**
   * Asserts that the text in the element corresponds to the clipboard.
   *
   * @param \Behat\Mink\Element\NodeElement $element
   *   The copyright element.
   * @param string $expected
   *   The expected result.
   */
  protected function assertCopyrightCopiedToClipboard(NodeElement $element, string $expected): void {
    $assert_session = $this->assertSession();

    $assert_session->elementExists('css', '.copyright-trigger', $element)->click();
    $modal = $assert_session->waitForElementVisible('css', '.modal.show');

    $assert_session->elementExists('css', '[data-copy-target]', $modal)->click();
    $actual = $this->getSession()->evaluateScript('return window.copiedText;');
    $this->assertEquals($expected, $actual);
    $this->getSession()->wait(1000, 'document.querySelectorAll(".modal.show").length === 0');
  }

}
