<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\FunctionalJavascript;

use Behat\Mink\Element\NodeElement;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests the AccessibleToggle behavior for modal and offcanvas.
 */
class AccessibleToggleTest extends WebDriverTestBase {

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
    'ui_patterns_library',
    'ui_patterns_settings',
  ];

  /**
   * Tests the ARIA attributes for modal and offcanvas triggers.
   */
  public function testAccessibleToggleAttributes(): void {
    $this->drupalLogin($this->drupalCreateUser([], NULL, TRUE));
    // The offcanvas toggle is only visible below Bootstrap's "lg" breakpoint
    // in the pattern preview, so resize the window to a mobile width.
    $this->getSession()->resizeWindow(600, 800);
    $this->drupalGet('/admin/appearance/ui/patterns');
    $assert = $this->assertSession();

    $modalSelector = '[data-bs-toggle="modal"]';
    $offcanvasSelector = '[data-bs-toggle="offcanvas"]';

    $modalTrigger = $assert->waitForElementVisible('css', "{$modalSelector}[aria-haspopup=\"dialog\"]");
    $offcanvasTrigger = $assert->waitForElementVisible('css', "{$offcanvasSelector}[aria-haspopup=\"dialog\"]");
    $offcanvasTarget = $offcanvasTrigger->getAttribute('data-bs-target');
    $this->assertNotEmpty($offcanvasTarget);

    $this->assertAccessibleAttributes($modalTrigger);
    $this->assertAccessibleAttributes($offcanvasTrigger, expanded: FALSE);
    $assert->elementExists('css', "{$offcanvasTarget}[role=\"region\"][data-offcanvas-static-role=\"region\"]");

    // Ensure late behavior attachment does not cache Bootstrap's dialog role.
    $this->getSession()->executeScript(<<<'JS'
      (function () {
        var offcanvas = document.createElement('div');
        offcanvas.id = 'late-offcanvas';
        offcanvas.classList.add('offcanvas-lg');
        offcanvas.setAttribute('role', 'dialog');
        offcanvas.setAttribute('data-offcanvas-static-role', 'complementary');
        document.body.appendChild(offcanvas);
        Drupal.behaviors.accessibleToggle.attach(document, drupalSettings);
        offcanvas.dispatchEvent(new Event('hidden.bs.offcanvas'));
      }());
    JS);
    $assert->elementExists('css', '#late-offcanvas[role="complementary"]');

    $this->clickWhenInViewport($modalSelector);
    $assert->waitForElementVisible('css', '.modal.show');
    $modalTrigger = $assert->waitForElement('css', "{$modalSelector}[aria-expanded=\"true\"]");
    $this->assertAccessibleAttributes($modalTrigger, expanded: TRUE);
    $this->assertAccessibleAttributes($offcanvasTrigger, expanded: FALSE);

    $this->clickWhenInViewport('.modal.show .btn-close');
    $modalTrigger = $assert->waitForElement('css', "{$modalSelector}[aria-expanded=\"false\"]");
    $this->assertTrue($assert->waitForElementRemoved('css', '.modal-backdrop'));
    $this->assertAccessibleAttributes($modalTrigger, expanded: FALSE);
    $this->assertAccessibleAttributes($offcanvasTrigger, expanded: FALSE);

    $this->clickWhenInViewport($offcanvasSelector);
    $assert->waitForElementVisible('css', "{$offcanvasTarget}.show[role=\"dialog\"][aria-modal=\"true\"]");
    $offcanvasTrigger = $assert->waitForElement('css', "{$offcanvasSelector}[aria-expanded=\"true\"]");
    $this->assertAccessibleAttributes($modalTrigger, expanded: FALSE);
    $this->assertAccessibleAttributes($offcanvasTrigger, expanded: TRUE);

    $this->clickWhenInViewport("{$offcanvasTarget}.show .btn-close");
    $offcanvasTrigger = $assert->waitForElement('css', "{$offcanvasSelector}[aria-expanded=\"false\"]");
    $assert->waitForElement('css', "{$offcanvasTarget}[role=\"region\"]:not([aria-modal])");
    $this->assertAccessibleAttributes($modalTrigger, expanded: FALSE);
    $this->assertAccessibleAttributes($offcanvasTrigger, expanded: FALSE);
  }

  /**
   * Asserts the initial ARIA attributes for the triggers.
   *
   * @param \Behat\Mink\Element\NodeElement|null $trigger
   *   The trigger element.
   * @param bool|null $expanded
   *   The expected state of aria-expanded attribute.
   */
  protected function assertAccessibleAttributes(?NodeElement $trigger, ?bool $expanded = NULL): void {
    $this->assertNotNull($trigger);
    $this->assertEquals('dialog', $trigger->getAttribute('aria-haspopup'));
    if ($expanded !== NULL) {
      $this->assertEquals($expanded ? 'true' : 'false', $trigger->getAttribute('aria-expanded'));
    }
  }

  /**
   * Scrolls an element into the viewport and clicks it.
   *
   * @param string $selector
   *   The CSS selector for the element.
   */
  protected function clickWhenInViewport(string $selector): void {
    $encodedSelector = json_encode($selector, JSON_THROW_ON_ERROR);
    $this->getSession()->executeScript(<<<JS
      (function () {
        var element = document.querySelector($encodedSelector);
        if (!element) {
          throw new Error('Element not found: ' + $encodedSelector);
        }
        element.scrollIntoView({block: 'center'});
      }());
    JS);
    $this->assertTrue($this->getSession()->wait(5000, <<<JS
      (function () {
        var element = document.querySelector($encodedSelector);
        if (!element) {
          return false;
        }
        var rect = element.getBoundingClientRect();
        return rect.top >= 0 && rect.left >= 0 &&
          rect.bottom <= window.innerHeight &&
          rect.right <= window.innerWidth;
      }())
    JS));
    $this->assertSession()->elementExists('css', $selector)->click();
  }

}
