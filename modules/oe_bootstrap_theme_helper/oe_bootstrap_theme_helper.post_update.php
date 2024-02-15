<?php

/**
 * @file
 * Post update functions for the OE Bootstrap theme helper module.
 */

declare(strict_types=1);

/**
 * Uninstall OE Bootstrap Theme Paragraphs module.
 */
function oe_bootstrap_theme_helper_post_update_00001(array &$sandbox): void {
  $moduleHandler = \Drupal::service('module_handler');
  if ($moduleHandler->moduleExists('oe_bootstrap_theme_paragraphs')) {
    \Drupal::service('module_installer')->uninstall(['oe_bootstrap_theme_paragraphs']);
  }
}
