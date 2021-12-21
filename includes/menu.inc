<?php

/**
 * @file
 * Theme and preprocess functions for menus.
 */

use Drupal\Core\Template\Attribute;

/**
 * Implements hook_preprocess_menu().
 */
function oe_bootstrap_theme_preprocess_menu(&$variables, $hook) {
  if ($hook === 'menu__toolbar') {
    return;
  }

  $items = $variables['items'];
  foreach ($items as $key => $item) {
    $class = $item['in_active_trail'] ? ['active'] : [];
    $menu_item = [
      'label' => $item['title'],
      'path' => $item['url']->toString(),
      'attributes' => new Attribute(['class' => $class]),
    ];
    $variables['items'][$key] = $menu_item;

    if (empty($item['below'])) {
      continue;
    }

    $class[] = 'nav-link';
    $menu_item['attributes'] = new Attribute(['class' => $class]);
    $item_with_submenu = [
      'standalone' => TRUE,
      'dropdown' => TRUE,
      'link' => TRUE,
      'alignment' => 'dropdown-menu-sm-start',
      'trigger' => $menu_item,
    ];
    $variables['items'][$key] = $item_with_submenu;

    foreach ($item['below'] as $key_sub_item => $sub_item) {
      $subclass = $sub_item['in_active_trail'] ? ['active'] : [];
      $sub_item_menu = [
        'label' => $sub_item['title'],
        'path' => $sub_item['url']->toString(),
        'attributes' => new Attribute(['class' => $subclass]),
      ];
      $variables['items'][$key]['items'][$key_sub_item] = $sub_item_menu;
    }
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

  foreach ($variables['primary'] as $link) {
    /** @var Drupal\Core\Url $url */
    $url = $link['#link']['url'];
    if ($url->access($variables['user'])) {
      $variables['primary_links'][] = [
        'link_style' => 'primary',
        'path' => $url->toString(),
        'label' => $link['#link']['title'],
        'standalone' => TRUE,
        'attributes' => $link['#active'] ? new Attribute(['class' => 'active']) : [],
      ];
    }
  }

  foreach ($variables['secondary'] as $link) {
    /** @var Drupal\Core\Url $url */
    $url = $link['#link']['url'];
    if ($url->access($variables['user'])) {
      $variables['primary_links'][] = [
        'link_style' => 'secondary',
        'path' => $url->toString(),
        'label' => $link['#link']['title'],
        'standalone' => TRUE,
        'attributes' => $link['#active'] ? new Attribute(['class' => 'active']) : [],
      ];
    }
  }
}
