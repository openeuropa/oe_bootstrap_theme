<?php

/**
 * @file
 * Theme and preprocess functions for paragraphs.
 */

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

  // Append view mode to default field suggestions, if any.
  if (isset($element['#view_mode'])) {
    $suggestions[] = 'field__' . $element['#field_type'] . '__' . $element['#view_mode'];
    $suggestions[] = 'field__' . $element['#field_name'] . '__' . $element['#view_mode'];
    $suggestions[] = 'field__' . $element['#entity_type'] . '__' . $element['#bundle'] . '__' . $element['#view_mode'];
    $suggestions[] = 'field__' . $element['#entity_type'] . '__' . $element['#field_name'] . '__' . $element['#view_mode'];
    $suggestions[] = 'field__' . $element['#entity_type'] . '__' . $element['#field_name'] . '__' . $element['#bundle'] . '__' . $element['#view_mode'];
  }
}

/**
 * Implements hook_theme_suggestions_HOOK_alter().
 */
function eua_theme_theme_suggestions_paragraph_alter(array &$suggestions, array $variables): void {
  /** @var \Drupal\paragraphs\ParagraphInterface $paragraph */
  $paragraph = $variables['elements']['#paragraph'];

  if ($paragraph->bundle() === 'oe_content_row') {
    $variant = $paragraph->get('oe_paragraphs_variant')->first()->value;
    $suggestions[] = 'paragraph__' . $paragraph->bundle() . '__variant_' . $variant;
  }
}

/**
 * Implements hook_preprocess_paragraph__oe_accordion().
 */
function eua_theme_preprocess_paragraph__oe_accordion(array &$variables): void {
  // Massage data to be compliant with ECL Accordion2 component data structure.
  $builder = \Drupal::entityTypeManager()->getViewBuilder('paragraph');
  $variables['identifier'] = 'paragraph-' . $variables['paragraph']->id();
  $variables['items'] = [];

  /** @var \Drupal\entity_reference_revisions\Plugin\Field\FieldType\EntityReferenceRevisionsItem $field_item */
  foreach ($variables['paragraph']->get('field_oe_paragraphs') as $field_item) {
    $paragraph = \Drupal::service('entity.repository')->getTranslationFromContext($field_item->entity);
    $variables['items'][] = [
      'title' => $builder->viewField($paragraph->get('field_oe_text')),
      'body' => $builder->viewField($paragraph->get('field_oe_text_long')),
    ];
  }
}
