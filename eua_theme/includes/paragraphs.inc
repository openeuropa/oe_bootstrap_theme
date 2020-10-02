<?php

/**
 * @file
 * Theme and preprocess functions for paragraphs.
 */

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
