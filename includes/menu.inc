<?php

/**
 * @file
 * Theme and preprocess functions for menus.
 */

declare(strict_types = 1);

use Drupal\Core\Template\Attribute;

/**
 * Implements hook_preprocess_menu().
 */
function oe_bootstrap_theme_preprocess_menu(&$variables, $hook) {
  if ($hook === 'menu__toolbar') {
    return;
  }

  foreach ($variables['items'] as &$item) {
    $item = _oe_bootstrap_theme_menu_link($item);

    if (empty($item['below'])) {
      continue;
    }

    foreach ($item['below'] as &$sub_item) {
      $sub_item = _oe_bootstrap_theme_menu_link($sub_item);
    }

    $item = [
      'standalone' => TRUE,
      'dropdown' => TRUE,
      'link' => TRUE,
      'trigger' => $item,
      'items' => $item['below'],
    ];
  }
}

/**
 * Implements template_preprocess_menu_local_action().
 */
function oe_bootstrap_theme_preprocess_menu_local_action(&$variables) {
  // Add button classes.
  $variables['link']['#options']['attributes']['class'][] = 'btn';
  $variables['link']['#options']['attributes']['class'][] = 'btn-sm';
  $variables['link']['#options']['attributes']['class'][] = 'btn-primary';
}

/**
 * Implements template_preprocess_links__dropbutton().
 */
function oe_bootstrap_theme_preprocess_links__dropbutton(&$variables) {
  $links = &$variables['links'];

  // Do nothing if we have no links.
  if (!count($links)) {
    return;
  }

  // Get the first link and use it for the dropbutton.
  $link = reset($links);

  /** @var \Drupal\Core\Url $url */
  $variables['split'] = FALSE;
  $button = $link['text'];
  if (isset($link['link']) && ($url = $link['link']['#url'])) {
    $button = $link['link'];

    if ($url->getRouteName() !== '<nolink>') {
      $variables['split'] = TRUE;
      $button['#options']['attributes']['class'][] = 'btn';
      $button['#options']['attributes']['class'][] = 'btn-sm';
      $button['#options']['attributes']['class'][] = 'btn-outline-primary';
    }

    // Remove first link from links.
    array_shift($links);
  }

  $variables['button'] = $button;
  oe_bootstrap_theme_add_dropbutton_classes($links);
}

/**
 * Adds required classes to dropbutton links.
 *
 * @param array $links
 *   Array with links items.
 */
function oe_bootstrap_theme_add_dropbutton_classes(array &$links): void {
  foreach ($links as $key => $link) {
    if (isset($links[$key]['link']) && is_array($links[$key]['link'])) {
      $links[$key]['link']['#options']['attributes']['class'][] = 'dropdown-item';
    }
    if (isset($links[$key]['text_attributes'])) {
      $links[$key]['text_attributes']->addClass('dropdown-item');
    }
    if (isset($links[$key]['attributes'])) {
      $links[$key]['attributes']->addClass('dropdown-item');
    }
    if (isset($links[$key]['text']) && is_array($links[$key]['text'])) {
      $links[$key]['text']['#attributes']['class'][] = 'dropdown-item';
    }
  }
}

/**
 * Implements hook_preprocess_menu_local_tasks().
 */
function oe_bootstrap_theme_preprocess_menu_local_tasks(array &$variables): void {
  // Sort local tasks by weight.
  uasort($variables['primary'], [
    '\Drupal\Component\Utility\SortArray',
    'sortByWeightProperty',
  ]);
  uasort($variables['secondary'], [
    '\Drupal\Component\Utility\SortArray',
    'sortByWeightProperty',
  ]);

  $links = [];
  foreach ($variables['primary'] as $link) {
    if (_oe_bootstrap_theme_prepare_local_task_link($link)) {
      $links[] = $link;
    }
  }
  $variables['primary'] = $links;

  $links = [];
  foreach ($variables['secondary'] as $link) {
    if (_oe_bootstrap_theme_prepare_local_task_link($link)) {
      $links[] = $link;
    }
  }
  $variables['secondary'] = $links;
}

/**
 * Prepare a local task menu link.
 *
 * @param array $link
 *   The menu link to alter.
 *
 * @return bool
 *   The access result check.
 */
function _oe_bootstrap_theme_prepare_local_task_link(array &$link): bool {
  if ($link['#access']->isForbidden()) {
    return FALSE;
  }
  if ($link['#active']) {
    $link['#link']['in_active_trail'] = TRUE;
  }
  $link = _oe_bootstrap_theme_menu_link($link['#link']);

  return TRUE;
}

/**
 * Adapts a menu link to BCL requirements.
 *
 * @param array $menu_link
 *   The menu link to alter.
 *
 * @return array
 *   The altered menu link.
 */
function _oe_bootstrap_theme_menu_link(array $menu_link): array {
  $link = $menu_link + [
    'label' => $menu_link['title'],
    'path' => $menu_link['url'],
  ];
  $attributes = $menu_link['attributes'] ?? new Attribute();
  $attributes->addClass('nav-link');

  if (!empty($menu_link['in_active_trail'])) {
    $attributes->addClass('active');
  }

  $link['attributes'] = $attributes;

  return $link;
}
