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
  $configs = [
    'field.storage.paragraph.oe_bt_orientation',
    'field.field.paragraph.oe_description_list.oe_bt_orientation',
    'core.entity_form_display.paragraph.oe_description_list.default',
    'core.entity_view_display.paragraph.oe_description_list.default',
  ];

  ConfigImporter::importMultiple('oe_bootstrap_theme_paragraphs', '/config/post_updates/00001/', $configs);
}
