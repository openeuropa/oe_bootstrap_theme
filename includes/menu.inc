<?php

/**
 * @file
 * Theme and preprocess functions for menus.
 */

declare(strict_types = 1);

use Drupal\Core\Link;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Url;

/**
 * Implements hook_preprocess_menu().
 */
function oe_bootstrap_theme_preprocess_menu(array &$variables, string $hook): void {
  if ($hook === 'menu__toolbar') {
    return;
  }

  // Merge additional attributes into the URL object.
  $prepare_url = function (array $item): Url {
    // Don't modify the original URL object.
    $url = clone $item['url'];

    // Merge passed attributes into the URL object.
    $passed_attributes = [];
    if (!empty($item['attributes'])) {
      $passed_attributes = $item['attributes'] instanceof Attribute ? $item['attributes']->toArray() : $item['attributes'];
    }
    $attributes = [
      'class' => $item['in_active_trail'] ? ['active'] : [],
    ] + $passed_attributes;

    $url->mergeOptions(['attributes' => $attributes]);

    return $url;
  };

  $variables['pattern_items'] = [];
  foreach ($variables['items'] as $key => $item) {
    $url = $prepare_url($item);
    $pattern_item = [
      'label' => $item['title'],
      'url' => $item['url'],
      'attributes' => new Attribute($url->getOption('attributes')),
      'link' => (new Link($item['title'], $item['url']))->toRenderable(),
      'path' => $item['url']->toString(),
    ];
    if (empty($item['below'])) {
      $variables['pattern_items'][$key] = $pattern_item;
      continue;
    }

    $submenu = $pattern_item;
    $submenu['attributes'] = new Attribute(['class' => 'nav-link']);
    $pattern_item += [
      'standalone' => TRUE,
      'dropdown' => TRUE,
      'link' => TRUE,
      'alignment' => 'dropdown-menu_item-sm-start',
      'trigger' => $submenu,
    ];
    foreach ($item['below'] as $key_sub_item => $sub_item) {
      $url = $prepare_url($sub_item);
      $pattern_item['items'][$key_sub_item] = [
        'label' => $sub_item['title'],
        'url' => $sub_item['url'],
        'attributes' => new Attribute($url->getOption('attributes')),
        'link' => (new Link($sub_item['title'], $sub_item['url']))->toRenderable(),
        'path' => $sub_item['url']->toString(),
      ];
    }
    $variables['pattern_items'][$key] = $pattern_item;
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
    oe_bootstrap_theme_prepare_menu_local_task_links($link, $variables, 'primary');
  }

  foreach ($variables['secondary'] as $link) {
    oe_bootstrap_theme_prepare_menu_local_task_links($link, $variables, 'secondary');
  }
}

/**
 * Prepare menu local tasks render links.
 *
 * @param array $link
 *   Array with the links items to be prepared.
 * @param array $variables
 *   The render array.
 * @param string $type
 *   Discriminate between primary or secondary.
 */
function oe_bootstrap_theme_prepare_menu_local_task_links(array $link, array &$variables, string $type): void {
  /** @var Drupal\Core\Url $url */
  $url = $link['#link']['url'];
  if ($url->access($variables['user'])) {
    $variables[$type . '_links'][] = [
      'link_style' => $type,
      'path' => $url->toString(),
      'label' => $link['#link']['title'],
      'standalone' => TRUE,
      'attributes' => $link['#active'] ? new Attribute(['class' => 'active']) : [],
    ];
  }
}
