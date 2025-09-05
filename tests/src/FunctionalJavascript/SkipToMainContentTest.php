<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Verifies the "Skip to main content" link focuses <main id="main-content">.
 */
class SkipToMainContentTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'oe_bootstrap_theme';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'oe_bootstrap_theme_helper',
  ];

  /**
   * Ensures the skip link focuses the main landmark.
   */
  public function testSkipToMainContentFocusesMain(): void {
    $this->drupalGet('<front>');
    $assert = $this->assertSession();
    $skip_link = $assert->elementExists('css', 'a[href="#main-content"]');
    $this->getSession()->executeScript(<<<'JS'
      (function () {
        var a = document.querySelector('a[href="#main-content"]');
        if (!a) return;
        a.focus();
        a.click();
      }());
    JS);
    $hash = $this->getSession()->evaluateScript('return window.location.hash;');
    $this->assertSame('#main-content', $hash);
    $active_id = $this->getSession()->evaluateScript('return document.activeElement ? document.activeElement.id : "";');
    $this->assertSame('main-content', $active_id);
    $main = $assert->elementExists('css', 'main#main-content');
    $this->assertSame('-1', $main->getAttribute('tabindex'));
  }

}
