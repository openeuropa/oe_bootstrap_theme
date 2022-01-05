<?php

/**
 * @file
 * Post update functions for the OE Bootstrap theme paragraphs module.
 */

declare(strict_types = 1);

use Drupal\field\Entity\FieldConfig;

/**
 * Change label of fields of Text feature media paragraph.
 */
function oe_bootstrap_theme_paragraphs_post_update_10001(array &$sandbox): void {
  $elements = [
    [
      'config_name' => 'field.field.paragraph.oe_text_feature_media.field_oe_title ',
      'field_name' => 'field_oe_title',
      'paragraph_name' => 'Oe text feature media',
      'label' => 'Title',
    ],
    [
      'config_name' => 'field.field.paragraph.oe_text_feature_media.field_oe_feature_media_title',
      'field_name' => 'field_oe_feature_media_title',
      'paragraph_name' => 'Oe text feature media',
      'label' => 'Media title',
    ],
  ];

  foreach ($elements as $element) {
    $field = FieldConfig::load($element['config_name']);

    if (!$field) {
      sprintf('Could not load the ' . $element['field_name'] . ' in ' . $element['paragraph_name'] . ' paragraph.');
    }

    $field->setSetting('label', $element['label']);
    $field->save();
  }

}
