<?php

/**
 * @file
 * Theme and preprocess functions for patterns.
 */

use Drupal\Core\Url;

/**
 * Implements hook_preprocess_HOOK() for badge_preview patterns.
 */
function oe_bootstrap_theme_preprocess_pattern_badge__preview(array &$variables) {
  if (strpos($variables['variant'], 'link__') === 0) {
    $variables['url'] = '#';
  }
}

/**
 * Implements hook_preprocess_HOOK() for page templates.
 */
function oe_bootstrap_theme_preprocess_page(&$variables) {
  // @todo It would be better to have a setting, like bootstrap_barrio do with
  //   bootstrap_barrio_fluid_container ('container-fluid' : 'container')
  $variables['container'] = 'container';
}

/**
 * Implements hook_preprocess_HOOK() for card patterns.
 */
function oe_bootstrap_theme_preprocess_pattern_card(array &$variables) {
  if (array_key_exists('image', $variables) && is_array($variables['image'])) {
    foreach ($variables['image'] as &$item) {
      _oe_bootstrap_theme_add_card_image_class($item, $variables['variant']);
    }
  }
  if (array_key_exists('links', $variables) && is_array($variables['links'])) {
    foreach ($variables['links'] as &$item) {
      _oe_bootstrap_theme_add_card_link_class($item);
    }
  }
}

/**
 * Implements hook_preprocess_HOOK() for dropdown pattern.
 */
function oe_bootstrap_theme_preprocess_pattern_dropdown(array &$variables) {
  if (array_key_exists('items', $variables) && is_array($variables['items'])) {
    foreach ($variables['items'] as &$item) {
      $item['#attributes']['class'][] = 'dropdown-item';
    }
  }
}

/**
 * Implements hook_preprocess_HOOK() for dropdown pattern preview.
 */
function oe_bootstrap_theme_preprocess_pattern_dropdown__preview(array &$variables) {
  _oe_bootstrap_theme_preprocess_pattern_dropdown_preview($variables);
}

/**
 * Implements hook_preprocess_HOOK() for dropdown pattern custom preview.
 */
function oe_bootstrap_theme_preprocess_pattern_dropdown__variant_custom__preview(array &$variables) {
  _oe_bootstrap_theme_preprocess_pattern_dropdown_preview($variables);
}

/**
 * Convert URL string to object.
 */
function _oe_bootstrap_theme_preprocess_pattern_dropdown_preview(&$variables) {
  foreach ($variables['dropdown_items'] as &$item) {
    if (isset($item['#url'])) {
      $item['#url'] = Url::fromUri($item['#url']);
    }
  }
}

/**
 * Add expected class in card's image.
 */
function _oe_bootstrap_theme_add_card_image_class(&$item, $variant) {
  $class = 'card-img-top';
  if ($variant === 'overlay') {
    $class = 'card-img';
  }
  if (is_array($item) && array_key_exists('#theme', $item)) {
    if ($item['#theme'] === 'image') {
      $item['#attributes']['class'][] = $class;
    }
    if ($item['#theme'] === 'image_formatter') {
      $item['#item_attributes']['class'][] = $class;
    }
  }
  if (is_array($item)) {
    foreach ($item as &$next) {
      _oe_bootstrap_theme_add_card_image_class($next, $variant);
    }
  }
}

/**
 * Add expected class in card's link.
 */
function _oe_bootstrap_theme_add_card_link_class(&$item) {
  $class = 'card-link';
  if (is_array($item) && array_key_exists('#type', $item)) {
    if ($item['#type'] === 'link') {
      $item['#attributes']['class'][] = $class;
    }
    if ($item['#type'] === 'html_tag' && $item['#tag'] === 'a') {
      $item['#attributes']['class'][] = $class;
    }
  }
  if (is_array($item)) {
    foreach ($item as &$next) {
      _oe_bootstrap_theme_add_card_link_class($next);
    }
  }
}

/**
 * Implements hook_preprocess_HOOK() for carousel patterns.
 */
function oe_bootstrap_theme_preprocess_pattern_carousel(array &$variables) {
  // Move first image of each slide in a specific array key.
  if (array_key_exists('slides', $variables) && is_array($variables['slides'])) {
    foreach ($variables['slides'] as &$slide) {
      $slide['image'] = _oe_bootstrap_theme_extract_carousel_image($slide);
    }
  }

  // Nicer preview with fixed width and local backgrounds.
  // @code
  // if ($variables['context']->getType() == 'preview') {
  //   $variables['attributes']['style'] = "width: 800px";
  // }
  // @endcode
}

/**
 * Extract image from carousel slide.
 */
function _oe_bootstrap_theme_extract_carousel_image(&$item) {
  if (is_array($item) && array_key_exists('#theme', $item)) {
    if ($item['#theme'] === 'image' || $item['#theme'] === 'image_formatter') {
      $image = $item;
      $item = [];
      return $image;
    }
  }
  if (isset($item) && is_array($item)) {
    foreach ($item as &$next) {
      $dig = _oe_bootstrap_theme_extract_carousel_image($next);
      if ($dig) {
        return $dig;
      }
    }
  }
  return FALSE;
}

/**
 * Implements hook_preprocess_HOOK() for description list pattern.
 */
function oe_bootstrap_theme_preprocess_pattern_description_list(&$variables) {
  // Multiple terms can be passed for each item. Process all the entries to
  // add path and size to icons, if not yet specified.
  foreach ($variables['items'] as &$item) {
    // Allow to pass a single term, without forcing users to pass an array with
    // the "label" key in it. This covers for a bug in the BCL pattern.
    if (!is_array($item['term'])) {
      $item['term'] = [
        ['label' => $item['term']],
      ];
      continue;
    }

    foreach ($item['term'] as &$term) {
      if (is_array($term) && !empty($term['icon'])) {
        // If the icon is not an array, the icon name has been passed.
        if (!is_array($term['icon'])) {
          $term['icon'] = [
            'name' => $term['icon'],
          ];
        }
        $term['icon'] += [
          'size' => 'xs',
          'path' => $variables['bcl_icon_path'],
        ];
      }
    }
  }
}
