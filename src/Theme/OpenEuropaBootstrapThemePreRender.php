<?php

namespace Drupal\oe_bootstrap_theme\Theme;

use Drupal\Core\Render\Element;
use Drupal\Core\Security\TrustedCallbackInterface;

/**
 * Implements trusted prerender callbacks for the OpenEuropa Bootstrap Theme.
 *
 * @internal
 */
class OpenEuropaBootstrapThemePreRender implements TrustedCallbackInterface {

  /**
   * Pre-render callback for 'checkbox' element type.
   *
   * @param array $element
   *   The element.
   *
   * @return array
   *   The updated element.
   *
   * @see \Drupal\Core\Render\Element\Checkbox
   */
  public static function checkbox(array $element): array {
    if (!empty($element['#switch'])) {
      // Add a class that will turn the checkbox into a toggle switch, see
      // https://v5.getbootstrap.com/docs/5.0/forms/checks-radios/#switches.
      $element['#wrapper_attributes']['class'][] = 'form-switch';
    }
    return $element;
  }

  /**
   * Pre-render callback for 'checkboxes' element type.
   *
   * @param array $element
   *   The element.
   *
   * @return array
   *   The updated element.
   *
   * @see \Drupal\Core\Render\Element\Checkboxes
   */
  public static function checkboxes(array $element): array {
    if (!empty($element['#switch'])) {
      // Enable the '#switch' behavior on each individual checkbox.
      $children = Element::children($element);
      foreach ($children as $child) {
        $element[$child]['#switch'] = TRUE;
      }
    }
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks(): array {
    return [
      'checkbox',
      'checkboxes',
    ];
  }

}
