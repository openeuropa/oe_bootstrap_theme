<?php

/**
 * @file
 * Post update functions for the OE Bootstrap theme helper module.
 */

declare(strict_types=1);

use Drupal\block\Entity\Block;
use Drupal\oe_bootstrap_theme\ConfigImporter;

/**
 * Uninstall OE Bootstrap Theme Paragraphs module.
 */
function oe_bootstrap_theme_helper_post_update_00001(array &$sandbox): void {
  $moduleHandler = \Drupal::service('module_handler');
  if ($moduleHandler->moduleExists('oe_bootstrap_theme_paragraphs')) {
    \Drupal::service('module_installer')->uninstall(['oe_bootstrap_theme_paragraphs']);
  }
}

/**
 * Place the OEL mega menu block.
 */
function oe_bootstrap_theme_helper_post_update_00002(): string {
  if (!\Drupal::moduleHandler()->moduleExists('block')) {
    return 'No blocks can be placed, because the block module is not installed.';
  }

  ConfigImporter::importSingle('module', 'oe_bootstrap_theme_helper', '/config/post_updates/00002_megamenu', 'block.block.oe_bootstrap_theme_oelmegamenu');

  $report = 'The new mega menu block was placed in the navigation region for oe_bootstrap_theme.';

  $old_navigation_block = Block::load('oe_bootstrap_theme_mainnavigation');
  if (!$old_navigation_block || !$old_navigation_block->getTheme() !== 'oe_bootstrap_theme') {
    return $report . "\nThe old navigation block was not found.";
  }
  if (!$old_navigation_block->status()) {
    return $report . "\nThe old navigation block was already disabled.";
  }

  $old_navigation_block->setStatus(FALSE)->save();

  return $report . "\nThe old navigation block was disabled.";
}
