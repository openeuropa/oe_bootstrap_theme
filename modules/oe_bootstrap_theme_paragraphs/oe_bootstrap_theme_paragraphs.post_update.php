<?php

/**
 * @file
 * Post update functions for the OE Bootstrap theme paragraphs module.
 */

declare(strict_types = 1);

use Drupal\oe_bootstrap_theme\ConfigImporter;

/**
 * Add extra fields to description list paragraph.
 */
function oe_bootstrap_theme_paragraphs_post_update_00001(array &$sandbox): void {
  \Drupal::service('module_installer')->install(['oe_paragraphs_description_list']);

  $configs = [
    'field.storage.paragraph.oe_bt_orientation',
    'field.field.paragraph.oe_description_list.oe_bt_orientation',
    'core.entity_form_display.paragraph.oe_description_list.default',
    'core.entity_view_display.paragraph.oe_description_list.default',
  ];

  ConfigImporter::importMultiple('oe_bootstrap_theme_paragraphs', '/config/post_updates/00001/', $configs, TRUE);
}

/**
 * Hides the field icon at Accordion item paragraph.
 */
function oe_bootstrap_theme_paragraphs_post_update_00002(array &$sandbox): void {
  $configs = [
    'core.entity_form_display.paragraph.oe_accordion_item.default',
    'core.entity_view_display.paragraph.oe_accordion_item.default',
  ];

  ConfigImporter::importMultiple('oe_bootstrap_theme_paragraphs', '/config/post_updates/00002/', $configs, TRUE);
}

/**
 * Listing variants.
 */
function oe_bootstrap_theme_paragraphs_post_update_00003(array &$sandbox): void {
  $configs = [
    'core.entity_form_display.paragraph.oe_list_item_block.default',
    'core.entity_form_display.paragraph.oe_list_item_block.highlight',
    'core.entity_form_display.paragraph.oe_list_item.default',
  ];

  ConfigImporter::importMultiple('oe_bootstrap_theme_paragraphs', '/config/post_updates/00003/', $configs, TRUE);
}
