<?php

/**
 * @file
 * Theme and preprocess functions for paragraphs.
 */

use Drupal\Component\Utility\Html;

/**
 * Implements hook_preprocess_paragraph() for paragraph--oe-content-row--variant-inpage_navigation.html.twig.
 *
 * Prepares the inpage navigation by creating links for the inner paragraphs
 * that implement a title.
 */
function eua_theme_preprocess_paragraph__oe_content_row__variant_inpage_navigation(array &$variables): void {
  /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
  $paragraph = $variables['paragraph'];

  if ($paragraph->get('field_oe_paragraphs')->isEmpty()) {
    return;
  }

  // Add default fallback title for inpage naviagtion.
  $variables['title'] = t('Page contents');
  if (!$paragraph->get('field_oe_title')->isEmpty()) {
    $variables['title'] = $paragraph->get('field_oe_title')->value;
  }

  // Create a shortcut to the render array for the field.
  $field_render = &$variables['content']['field_oe_paragraphs'];
  $links = [];
  foreach ($paragraph->get('field_oe_paragraphs')->referencedEntities() as $delta => $sub_paragraph) {
    /** @var \Drupal\paragraphs\Entity\Paragraph $sub_paragraph */
    if (!$sub_paragraph->hasField('field_oe_title') || $sub_paragraph->get('field_oe_title')->isEmpty()) {
      continue;
    }

    $unique_id = Html::getUniqueId('ecl-inpage-' . $sub_paragraph->id());
    // Wrap the paragraph in a div with a specific id set as anchor.
    $field_render[$delta]['#theme_wrappers']['container'] = [
      '#attributes' => ['id' => $unique_id],
    ];

    // Get sub-paragraph translation.
    $sub_paragraph = \Drupal::service('entity.repository')
      ->getTranslationFromContext($sub_paragraph, $paragraph->language()->getId());

    // Add a link pointing to the paragraph.
    $links[] = [
      'url' => '#' . $unique_id,
      'title' => $sub_paragraph->get('field_oe_title')->first()->value,
    ];
  }

  $variables['links'] = $links;
}
