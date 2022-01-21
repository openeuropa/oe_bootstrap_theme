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
 * Implements hook_preprocess_pattern() for pattern listing.
 */
function oe_bootstrap_theme_preprocess_pattern_listing(array &$variables): void {
  if (isset($variables['items'])) {
    // @todo Align this with card after refactoring.
    foreach ($variables['items'] as $key => $value) {
      if (isset($variables['items'][$key]['date'])) {
        $variables['items'][$key]['date']['attributes'] = new Attribute();
      }
      $item = &$variables['items'][$key];

      $item['title'] = [
        'content' => $item['title'],
      ];
      $item['text'] = [
        'content' => $item['text'],
        'classes' => $variables['variant'] === 'highlight' ? 'mb-2' : 'mb-3',
      ];

      if (!empty($item['image'])) {
        $item['image']['position'] = 'top';
        $item['image']['rounded'] = 1;
      }

      if (isset($item['badges'])) {
        $metas = [];
        foreach ($item['badges'] as $item_meta) {
          $metas[] = [
            'label' => $item_meta,
            'background' => 'primary',
          ];
        }
        $item['badges'] = $metas;
      }

      if (isset($item['content']['date'])) {
        $item['content'] = [
          '#type' => 'inline_template',
          '#template' => '<div><span class="text-muted d-md-inline d-block me-4 mb-2 mb-md-0">{{ bundle_name }}</span><span class="d-md-inline d-block text-muted mb-2 mb-md-0">{{ date }}</span></div>',
          '#context' => [
            'date' => $item['content']['date'],
            'bundle_name' => $item['content']['bundle_name'],
          ],
        ];
      }
    }
  }
}
