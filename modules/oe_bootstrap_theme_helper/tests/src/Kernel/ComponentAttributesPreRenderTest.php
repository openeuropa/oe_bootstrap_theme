<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme_helper\Kernel;

use Drupal\Tests\oe_bootstrap_theme\Kernel\AbstractKernelTestBase;
use Drupal\oe_bootstrap_theme_helper\Render\ComponentAttributesPreRender;

/**
 * Tests the 'attributes' prop conversion on the component render element.
 *
 * @todo Remove when support for Drupal < 11.3 is dropped.
 */
class ComponentAttributesPreRenderTest extends AbstractKernelTestBase {

  /**
   * Tests that the callback is added only where core lacks the conversion.
   */
  public function testElementInfo(): void {
    $pre_render = $this->container->get('element_info')->getInfo('component')['#pre_render'];
    $callback = [ComponentAttributesPreRender::class, 'preRender'];
    if (ComponentAttributesPreRender::isNeeded()) {
      $this->assertSame($callback, $pre_render[0]);
    }
    else {
      $this->assertNotContains($callback, $pre_render);
    }
  }

}
