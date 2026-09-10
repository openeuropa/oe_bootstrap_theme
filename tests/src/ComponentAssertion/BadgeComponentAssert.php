<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\ComponentAssertion;

use Drupal\Tests\oe_bootstrap_theme\PatternAssertion\BadgePatternAssert;

/**
 * Assertions for the badge component.
 */
class BadgeComponentAssert extends BadgePatternAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions(string $variant): array {
    return [
      'label' => [
        [$this, 'assertLabel'],
      ],
      'url' => [
        [$this, 'assertElementAttribute'],
        '.badge',
        'href',
      ],
      'title' => [
        [$this, 'assertElementAttribute'],
        '.badge',
        'title',
      ],
      'assistive_text' => [
        [$this, 'assertAssistiveText'],
      ],
      'background' => [
        [$this, 'assertBackground'],
      ],
      'rounded_pill' => [
        [$this, 'assertRoundedPill'],
      ],
      'dismissible' => [
        [$this, 'assertDismissible'],
      ],
      'outline' => [
        [$this, 'assertOutline'],
      ],
    ];
  }

}
