<?php

/**
 * @file
 * Post update functions for the OE Bootstrap theme paragraphs module.
 */

declare(strict_types = 1);

use Drupal\oe_bootstrap_theme\ConfigUtil;

/**
 * Set new label for fields of Featured media paragraph.
 */
function oe_bootstrap_theme_paragraphs_post_update_10001(array &$sandbox): void {

  $config['fields'] = [
    'field.field.paragraph.oe_text_feature_media.field_oe_feature_media_title',
    'field.field.paragraph.oe_text_feature_media.field_oe_title',
  ];

  ConfigUtil::importConfigFromFile('oe_bootstrap_theme_paragraphs', '/config/post_updates/10001/', $config);

}
