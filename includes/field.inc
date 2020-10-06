<?php

/**
 * @file
 * Theme and preprocess functions for fields.
 */

/**
 * Implements hook_preprocess_field().
 */
function eua_theme_preprocess_field(&$variables) {
  $element = $variables['element'];
  $field_name = $element['#field_name'];
  $bundle = $element['#bundle'];

  // Add bundle to template.
  $variables['bundle'] = $bundle;

  // Add a clean field name without the field_BUNDLE_ prefix.
  $safe_field_name_prefix = 'field_' . $bundle . '_';
  $variables['field_name_clean'] = str_replace($safe_field_name_prefix, '', $field_name);
}

/**
 * Implements hook_theme_suggestions_HOOK_alter().
 *
 * Adds a bare, markup-free template suggestion to all paragraph fields.
 */
function eua_theme_theme_suggestions_field_alter(array &$suggestions, array $variables): void {
  $element = $variables['element'];

  // Do not output field labels and wrapping markup for paragraph fields.
  if (isset($element['#entity_type']) && $element['#entity_type'] === 'paragraph') {
    // Prepend the new suggestion to the list. This will put it right after the
    // default field template. By doing this we allow to override single
    // fields, while keeping all the rest markup-free.
    array_unshift($suggestions, 'field__bare');
  }
}
