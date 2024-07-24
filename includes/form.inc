<?php

/**
 * @file
 * Theme and preprocess functions for forms.
 */

declare(strict_types=1);

use Drupal\Core\Form\FormStateInterface;
use Drupal\oe_bootstrap_theme\BackwardCompatibility;

/**
 * Implements hook_theme_suggestions_HOOK_alter().
 */
function oe_bootstrap_theme_theme_suggestions_form_alter(array &$suggestions, array &$variables) {
  $element = $variables['element'];

  // Add a suggestion based on the form id name.
  if (isset($element['#form_id'])) {
    $suggestions[] = $variables['theme_hook_original'] . '__' . $element['#form_id'];
  }
}

/**
 * Implements hook_theme_suggestions_HOOK_alter().
 */
function oe_bootstrap_theme_theme_suggestions_form_element_alter(array &$suggestions, array &$variables) {
  $element = $variables['element'];

  // Add a suggestion based on the element type.
  if (isset($element['#type'])) {
    $suggestions[] = $variables['theme_hook_original'] . '__' . $element['#type'];
  }
}

/**
 * Implements hook_preprocess_input().
 */
function oe_bootstrap_theme_preprocess_input(&$variables) {
  $element = $variables['element'];

  // Add the element type to the theme.
  $variables['type'] = $element['#type'];
}

/**
 * Implements hook_preprocess_form_element().
 */
function oe_bootstrap_theme_preprocess_form_element(&$variables) {
  $element = $variables['element'];

  // Add column class.
  $variables['col_class'] = $element['#col_class'] ?? '';

  // Add default margin.
  $variables['margin_class'] = $element['#margin_class'] ?? 'mb-3';

  // Add required class for checkbox and radio labels.
  if (in_array($element['#type'], ['checkbox', 'radio'])) {
    $variables['label']['#attributes']['class'][] = 'form-check-label';
  }
}

/**
 * Implements hook_theme_HOOK_form_alter() for node add/edit.
 */
function oe_bootstrap_theme_form_node_form_alter(&$form, FormStateInterface $form_state) {
  $form['#theme'] = ['node_edit_form'];
}

/**
 * Implements hook_preprocess_HOOK() for fieldset template.
 */
function oe_bootstrap_theme_preprocess_fieldset(&$variables) {
  $variables['fieldset_wrapper_col_sm_10'] = BackwardCompatibility::getSetting('fieldset_wrapper_col_sm_10');
}
