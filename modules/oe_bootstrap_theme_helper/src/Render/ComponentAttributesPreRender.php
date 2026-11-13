<?php

declare(strict_types=1);

namespace Drupal\oe_bootstrap_theme_helper\Render;

use Drupal\Core\Render\Element\ComponentElement;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\Core\Template\Attribute;

/**
 * Converts an array in the 'attributes' prop of a component to an Attribute.
 *
 * Backport of ComponentElement::mergeElementAttributesToPropAttributes() from
 * Drupal 11.3. Before Drupal 11.3 the array reaches the component unconverted,
 * and fails the prop validation, which expects an Attribute object.
 *
 * @see https://www.drupal.org/node/3550051
 *
 * @todo Remove when support for Drupal < 11.3 is dropped.
 */
final class ComponentAttributesPreRender implements TrustedCallbackInterface {

  /**
   * Determines whether core lacks the conversion.
   *
   * @return bool
   *   TRUE if the callback has to be added to the component element.
   */
  public static function isNeeded(): bool {
    return !method_exists(ComponentElement::class, 'mergeElementAttributesToPropAttributes');
  }

  /**
   * Pre-render callback for the 'component' render element.
   *
   * @param array $element
   *   The render element.
   *
   * @return array
   *   The render element, with the 'attributes' prop as an Attribute object.
   */
  public static function preRender(array $element): array {
    if (is_array($element['#props']['attributes'] ?? NULL)) {
      $element['#props']['attributes'] = new Attribute($element['#props']['attributes']);
    }
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks(): array {
    return ['preRender'];
  }

}
