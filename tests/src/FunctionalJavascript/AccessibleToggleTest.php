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
    $this->drupalGet('/admin/appearance/ui/patterns');

    $modalTrigger = $this->getSession()->getPage()->find('css', '[data-bs-toggle="modal"]');
    $offcanvasTrigger = $this->getSession()->getPage()->find('css', '[data-bs-toggle="offcanvas"]');

    $this->assertAccessibleAttributes($modalTrigger);
    $this->assertAccessibleAttributes($offcanvasTrigger);

    $modalTrigger->click();
    $this->assertEquals('true', $modalTrigger->getAttribute('aria-expanded'));
    $this->assertEquals('false', $offcanvasTrigger->getAttribute('aria-expanded'));

    $this->getSession()->getPage()->find('css', '.modal .btn-close')->click();
    $this->assertEquals('false', $modalTrigger->getAttribute('aria-expanded'));
    $this->assertEquals('false', $offcanvasTrigger->getAttribute('aria-expanded'));

    $offcanvasTrigger->click();
    $this->assertEquals('false', $modalTrigger->getAttribute('aria-expanded'));
    $this->assertEquals('true', $offcanvasTrigger->getAttribute('aria-expanded'));

    // Close offcanvas and check attribute updates.
    $this->getSession()->getPage()->find('css', '.offcanvas-backdrop')->click();
    $this->assertEquals('false', $modalTrigger->getAttribute('aria-expanded'));
    $this->assertEquals('false', $offcanvasTrigger->getAttribute('aria-expanded'));
  }

  /**
   * Asserts the initial ARIA attributes for the triggers.
   *
   * @param \Behat\Mink\Element\NodeElement|null $trigger
   *   The trigger element.
   */
  protected function assertAccessibleAttributes(?NodeElement $trigger): void {
    $this->assertNotNull($trigger);
    $this->assertEquals('true', $trigger->getAttribute('aria-haspopup'));
    $this->assertEquals('false', $trigger->getAttribute('aria-expanded'));
  }

}
