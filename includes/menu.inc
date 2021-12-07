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
  // No changes for menu toolbar.
  if ($hook === 'menu__toolbar') {
    return;
  }

  // Get the current path.
  $current_path = \Drupal::request()->getRequestUri();

  $items = $variables['items'];

  foreach ($items as $key => $item) {
    $class = $item['in_active_trail'] ? ['active'] : [];
    if (!empty($item['below'])) {
      $class[] = 'nav-link';
      $variables['items'][$key]['dropdown'] = TRUE;
      $variables['items'][$key]['trigger']['label'] = $item['title'];
      $variables['items'][$key]['trigger']['path'] = $item['url']->toString();
      $variables['items'][$key]['trigger']['standalone'] = TRUE;
      $variables['items'][$key]['trigger']['attributes'] = new Attribute(['class' => $class]);
      $variables['items'][$key]['link'] = TRUE;
      $variables['items'][$key]['alignment'] = 'dropdown-menu-sm-start';
      foreach ($item['below'] as $key_subitem => $subitem) {
        $subclass = $subitem['in_active_trail'] ? ['active'] : [];
        $variables['items'][$key]['items'][$key_subitem]['label'] = $subitem['title'];
        $variables['items'][$key]['items'][$key_subitem]['path'] = $subitem['url']->toString();
        $variables['items'][$key]['items'][$key_subitem]['attributes'] = new Attribute(['class' => $subclass]);
      }
      continue;
    }
    $variables['items'][$key]['label'] = $item['title'];
    $variables['items'][$key]['path'] = $item['url']->toString();
    // If path is current_path, set active to li.
    $variables['items'][$key]['attributes']['class'] = $class;
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
