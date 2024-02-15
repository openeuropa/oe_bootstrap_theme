<?php

/**
 * @file
 * Theme and preprocess functions for fields.
 */

declare(strict_types=1);

/**
 * Implements hook_preprocess_field().
 */
function oe_bootstrap_theme_preprocess_field(&$variables) {
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
 */
function oe_bootstrap_theme_theme_suggestions_field_alter(array &$suggestions, array $variables): void {
  $element = $variables['element'];

  // Add suggestions for all 'Entity reference revisions' for each target type.
  if ($element['#field_type'] === 'entity_reference_revisions') {
    /** @var \Drupal\field\Entity\FieldConfig $field_definition */
    $field_definition = $element['#items']->getFieldDefinition();
    // Add suggestions based on the referenced entity type.
    $suggestions[] = 'field__entity_reference_revisions__' . $field_definition->getSetting('target_type');
    $suggestions[] = 'field__entity_reference_revisions__' . $field_definition->getSetting('target_type') . '__' . $field_definition->getName();

    // Add suggestions based on the parent entity and bundle types.
    $suggestions[] = 'field__entity_reference_revisions__' . $element['#entity_type'] . '__' . $element['#bundle'];
    $suggestions[] = 'field__entity_reference_revisions__' . $element['#entity_type'] . '__' . $element['#bundle'] . '__' . $field_definition->getName();
  }
}
