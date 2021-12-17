<?php

/**
 * @file
 * Post update functions for the OE Bootstrap theme paragraphs module.
 */

declare(strict_types = 1);

use Drupal\oe_bootstrap_theme\ConfigUtil;

/**
 * Upgrade the Description list paragraph.
 *
 * @param array $sandbox
 *   Stores information for batch updates.
 */
function oe_bootstrap_theme_paragraphs_post_update_10001(array &$sandbox): void {

  $config['fields'] = [
    'field.storage.paragraph.oe_bt_orientation',
    'field.field.paragraph.oe_description_list.oe_bt_orientation',
  ];

  $config['displays'] = [
    'core.entity_form_display.paragraph.oe_description_list.default',
    'core.entity_view_display.paragraph.oe_description_list.default',
  ];

  ConfigUtil::importConfigFromFile('oe_bootstrap_theme_paragraphs', '/config/post_updates/10001/', $config);

}
