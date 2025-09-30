<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Traits;

use Behat\Mink\Element\Element;
use Behat\Mink\Element\NodeElement;

/**
 * Contains a method to get select options.
 */
trait GetSelectOptionsTrait {

  /**
   * Helper function to get the options of select field.
   *
   * This replaces the core method which is now deprecated.
   *
   * @param \Behat\Mink\Element\NodeElement|string $select
   *   Name, ID, or Label of select field to assert.
   * @param \Behat\Mink\Element\Element|null $container
   *   (optional) Container element to check against. Defaults to current page.
   *
   * @return array<string, string>
   *   Associative array of option keys and values.
   */
  protected function getSelectOptions(string|NodeElement $select, ?Element $container = NULL): array {
    if (is_string($select)) {
      $select = $this->assertSession()->selectExists($select, $container);
    }
    $options = [];
    /** @var \Behat\Mink\Element\NodeElement $option */
    foreach ($select->findAll('xpath', '//option') as $option) {
      $label = $option->getText();
      $value = $option->getAttribute('value') ?: $label;
      $options[$value] = $label;
    }
    return $options;
  }

}
