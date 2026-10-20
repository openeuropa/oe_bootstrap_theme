<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Unit;

use Drupal\Tests\oe_bootstrap_theme\Kernel\fixtures\OptionalPropsNullifier;
use Drupal\Tests\UnitTestCase;

/**
 * Checks that NULL coverage does not change the meaning of nested inputs.
 */
class OptionalPropsNullifierTest extends UnitTestCase {

  /**
   * Missing item flags and icon paths must remain omitted.
   */
  public function testNestedInputsArePreserved(): void {
    $schema = [
      'required' => ['required_prop'],
      'properties' => [
        'required_prop' => ['type' => 'string'],
        'title' => ['type' => ['string', 'null']],
        'disabled' => ['type' => ['boolean', 'null']],
        'items' => [
          'type' => 'array',
          'items' => [
            'type' => 'object',
            'properties' => [
              'button' => ['type' => ['boolean', 'null']],
              'divider' => ['type' => ['boolean', 'null']],
              'icon' => [
                'type' => 'object',
                'properties' => ['path' => ['type' => ['string', 'null']]],
              ],
            ],
          ],
        ],
      ],
    ];
    $props = [
      'disabled' => FALSE,
      'items' => [
        ['label' => 'Link', 'icon' => ['name' => 'check']],
        ['label' => 'Button', 'button' => NULL, 'icon' => ['name' => 'check', 'path' => NULL]],
      ],
    ];
    $this->assertSame($props + ['title' => NULL], OptionalPropsNullifier::populate($schema, $props));
  }

}
