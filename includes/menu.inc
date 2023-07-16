<?php

/**
 * @file
 * Theme and preprocess functions for menus.
 */

declare(strict_types = 1);

use Drupal\oe_bootstrap_theme\MenuPreprocess;

/**
 * Implements hook_preprocess_menu().
 */
function oe_bootstrap_theme_preprocess_menu(&$variables, $hook) {
  /** @var \Drupal\oe_bootstrap_theme\MenuPreprocess $menu_preprocess */
  $menu_preprocess = \Drupal::classResolver(MenuPreprocess::class);
  $menu_preprocess->preprocessMenu($variables, $hook);
}

/**
 * Implements template_preprocess_menu_local_action().
 */
function oe_bootstrap_theme_preprocess_menu_local_action(&$variables) {
  /** @var \Drupal\oe_bootstrap_theme\MenuPreprocess $menu_preprocess */
  $menu_preprocess = \Drupal::classResolver(MenuPreprocess::class);
  $menu_preprocess->preprocessMenuLocalAction($variables);
}

/**
 * Implements template_preprocess_links__dropbutton().
 */
function oe_bootstrap_theme_preprocess_links__dropbutton(&$variables) {
  /** @var \Drupal\oe_bootstrap_theme\MenuPreprocess $menu_preprocess */
  $menu_preprocess = \Drupal::classResolver(MenuPreprocess::class);
  $menu_preprocess->preprocessLinksDropbutton($variables);
}

/**
 * Implements hook_preprocess_menu_local_tasks().
 */
function oe_bootstrap_theme_preprocess_menu_local_tasks(array &$variables): void {
  /** @var \Drupal\oe_bootstrap_theme\MenuPreprocess $menu_preprocess */
  $menu_preprocess = \Drupal::classResolver(MenuPreprocess::class);
  $menu_preprocess->preprocessMenuLocalTasks($variables);
}
