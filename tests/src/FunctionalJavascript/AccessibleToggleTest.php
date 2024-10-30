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

    $this->assertAccessibleAttributes($modalTrigger, expanded: FALSE);
    $this->assertAccessibleAttributes($offcanvasTrigger, expanded: FALSE);

    $modalTrigger->click();
    $this->assertAccessibleAttributes($modalTrigger, expanded: TRUE);
    $this->assertAccessibleAttributes($offcanvasTrigger, expanded: FALSE);

    $this->getSession()->getPage()->find('css', '.modal .btn-close')->click();
    $this->assertAccessibleAttributes($modalTrigger, expanded: FALSE);
    $this->assertAccessibleAttributes($offcanvasTrigger, expanded: FALSE);

    $offcanvasTrigger->click();
    $this->assertAccessibleAttributes($modalTrigger, expanded: FALSE);
    $this->assertAccessibleAttributes($offcanvasTrigger, expanded: TRUE);

    // Close offcanvas and check attribute updates.
    $this->getSession()->getPage()->find('css', '.offcanvas-backdrop')->click();
    $this->assertAccessibleAttributes($modalTrigger, expanded: FALSE);
    $this->assertAccessibleAttributes($offcanvasTrigger, expanded: FALSE);
  }

  /**
   * Asserts the initial ARIA attributes for the triggers.
   *
   * @param \Behat\Mink\Element\NodeElement|null $trigger
   *   The trigger element.
   * @param bool $expanded
   *   The expected state of aria-expanded attribute.
   */
  protected function assertAccessibleAttributes(?NodeElement $trigger, bool $expanded): void {
    $this->assertNotNull($trigger);
    $this->assertEquals('true', $trigger->getAttribute('aria-haspopup'));
    $this->assertEquals($expanded ? 'true' : 'false', $trigger->getAttribute('aria-expanded'));
  }

}
