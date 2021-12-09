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
  if (isset($link['link']) && ($url = $link['link']['#url'])) {
    $button = $link['link'];

    if ($variables['split'] = $url->getRouteName() !== '<nolink>') {
      $button['#options']['attributes']['class'][] = 'btn';
      $button['#options']['attributes']['class'][] = 'btn-sm';
      $button['#options']['attributes']['class'][] = 'btn-outline-primary';
    }

    $variables['button'] = $button;

    // Remove first link from links.
    array_shift($links);
  }

  // Add required classes.
  foreach ($links as $key => $link) {
    $links[$key]['link']['#options']['attributes']['class'][] = 'dropdown-item';

    if (isset($links[$key]['text_attributes'])) {
      $links[$key]['text_attributes']->addClass('dropdown-item');
    }

    if (isset($links[$key]['attributes'])) {
      $links[$key]['attributes']->addClass('dropdown-item');
    }
  }
}
