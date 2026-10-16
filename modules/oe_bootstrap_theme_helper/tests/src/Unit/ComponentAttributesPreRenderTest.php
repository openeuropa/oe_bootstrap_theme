<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme_helper\Unit;

use Drupal\Core\Template\Attribute;
use Drupal\Tests\UnitTestCase;
use Drupal\oe_bootstrap_theme_helper\Render\ComponentAttributesPreRender;

/**
 * Tests the 'attributes' prop conversion for the component render element.
 *
 * @todo Remove when support for Drupal < 11.3 is dropped.
 */
class ComponentAttributesPreRenderTest extends UnitTestCase {

  /**
   * Tests that an array is converted, and other values are left untouched.
   */
  public function testPreRender(): void {
    $element = ComponentAttributesPreRender::preRender([
      '#props' => [
        'attributes' => ['class' => ['foo'], 'id' => 'bar'],
        'title_attributes' => ['class' => ['baz']],
      ],
    ]);
    $this->assertInstanceOf(Attribute::class, $element['#props']['attributes']);
    $this->assertSame(['foo'], $element['#props']['attributes']['class']->value());
    $this->assertSame('bar', $element['#props']['attributes']['id']->value());
    // Other attribute props are not handled by core either.
    $this->assertSame(['class' => ['baz']], $element['#props']['title_attributes']);

    $attributes = new Attribute(['class' => ['foo']]);
    $element = ComponentAttributesPreRender::preRender(['#props' => ['attributes' => $attributes]]);
    $this->assertSame($attributes, $element['#props']['attributes']);

    foreach ([[], ['#props' => []], ['#props' => ['attributes' => NULL]]] as $element) {
      $this->assertSame($element, ComponentAttributesPreRender::preRender($element));
    }
  }

}
