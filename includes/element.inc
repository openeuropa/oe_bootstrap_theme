<?php

/**
 * @file
 * Theme and preprocess functions for elements.
 */

use Drupal\Core\Render\Element;
use Drupal\oe_bootstrap_theme\Theme\OpenEuropaBoostrapThemePreRender;

/**
 * Implements hook_element_info_alter().
 */
function oe_bootstrap_theme_element_info_alter(&$info) {
  if (isset($info['datetime'])) {
    $info['datetime']['#process'][] = '_oe_bootstrap_theme_process_element_grid_row';
  }

  if (isset($info['datelist'])) {
    $info['datelist']['#process'][] = '_oe_bootstrap_theme_process_element_grid_row';
  }

  // Add a pre-render function that handles checkbox variants.
  if (isset($info['checkbox'])) {
    $info['checkbox']['#pre_render'][] = [OpenEuropaBoostrapThemePreRender::class, 'checkbox'];
  }

  // Add a pre-render function that handles checkboxes variants.
  if (isset($info['checkboxes'])) {
    $info['checkboxes']['#pre_render'][] = [OpenEuropaBoostrapThemePreRender::class, 'checkboxes'];
  }
}

/**
 * Process callback for applying a grid on elements.
 *
 * @see oe_bootstrap_theme_element_info_alter
 */
function _oe_bootstrap_theme_process_element_grid_row(&$element) {
  $element['#attributes']['class'][] = 'row';
  foreach (Element::children($element) as $key) {
    $element[$key]['#col_class'] = 'col-auto';
  }
  return $element;
}
