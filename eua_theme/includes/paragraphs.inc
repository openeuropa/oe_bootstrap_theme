<?php

/**
 * @file
 * Theme and preprocess functions for paragraphs.
 */

/**
 * Implements hook_preprocess_paragraph__oe_accordion().
 */
function eua_theme_preprocess_paragraph__oe_accordion(array &$variables): void {
  // Massage data to be compliant with EUA accordion component data structure.
  $builder = \Drupal::entityTypeManager()->getViewBuilder('paragraph');
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
