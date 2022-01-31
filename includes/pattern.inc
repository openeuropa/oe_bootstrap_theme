<?php

/**
 * @file
 * Theme and preprocess functions for patterns.
 */

declare(strict_types = 1);

use Drupal\Component\Utility\Html;
use Drupal\oe_bootstrap_theme\ValueObject\ImageValueObject;
use Drupal\oe_bootstrap_theme\ValueObject\ImageValueObjectInterface;

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
 * Implements hook_preprocess_HOOK() for dropdown pattern.
 */
function oe_bootstrap_theme_preprocess_pattern_dropdown(array &$variables) {
  if (array_key_exists('items', $variables) && is_array($variables['items'])) {
    foreach ($variables['items'] as &$item) {
      $item['#attributes']['class'][] = 'dropdown-item';
    }
  }

  $variables['dropdown_id'] = Html::getUniqueId('bcl-dropdown');
}

/**
 * Implements hook_preprocess_HOOK() for carousel patterns.
 */
function oe_bootstrap_theme_preprocess_pattern_carousel(array &$variables) {
  $variables['carousel_id'] = Html::getUniqueId('bcl-carousel');
}

/**
 * Implements hook_preprocess_HOOK().
 */
function oe_bootstrap_theme_preprocess_pattern_offcanvas(array &$variables): void {
  $variables['offcanvas_id'] = $variables['attributes']['id'] ?? Html::getUniqueId('bcl_offcanvas');
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

/**
 * Implements hook_preprocess_HOOK().
 */
function oe_bootstrap_theme_preprocess_pattern_accordion(array &$variables): void {
  $variables['accordion_id'] = Html::getUniqueId('bcl-accordion');
}

/**
 * Implements hook_preprocess_HOOK().
 */
function oe_bootstrap_theme_preprocess_pattern_modal(array &$variables): void {
  $variables['modal_id'] = $variables['attributes']['id'] ?? Html::getUniqueId('bcl-modal');
}

/**
 * Implements hook_preprocess_HOOK().
 */
function oe_bootstrap_theme_preprocess_pattern_link(array &$variables): void {
  if (!isset($variables['icon'])) {
    return;
  }

  // Convert to array when only the icon name has been passed.
  if (!is_array($variables['icon'])) {
    $variables['icon'] = [
      'name' => $variables['icon'],
    ];
  }

  $variables['icon'] += [
    'path' => $variables['bcl_icon_path'],
  ];
}

/**
 * Implements hook_preprocess_HOOK().
 */
function oe_bootstrap_theme_preprocess_pattern_navbar(array &$variables): void {
  $variables['collapse_id'] = Html::getUniqueId('bcl-navbar');
}

/**
 * Implements hook_preprocess_HOOK() for featured media pattern.
 */
function oe_bootstrap_theme_preprocess_pattern_featured_media(&$variables) {
  if (!isset($variables['image'])) {
    return;
  }

  // This conversion is needed only to run markup rendering tests, as they
  // are reading from a yaml file where we cannot specify objects.
  // Do not ever rely on this for your patterns.
  // @todo Remove this! Code duplication below is intentional, these lines are
  //   to be removed, do not refactor unless to delete them.
  if (is_array($variables['image'])) {
    $variables['image'] = ImageValueObject::fromArray($variables['image']);
  }

  if ($variables['image'] instanceof ImageValueObjectInterface) {
    // Transform an object implementing ImageValueObjectInterface to our own
    // implementation, so that we can use the specific method.
    $variables['image'] = ImageValueObject::fromArray($variables['image']->getArray())
      ->toRenderArray();
  }
}

/**
 * Implements hook_preprocess_HOOK() for featured_media pattern preview.
 */
function oe_bootstrap_theme_preprocess_pattern_featured_media__preview(array &$variables) {
  if (isset($variables['image']) && is_array($variables['image'])) {
    $variables['image'] = ImageValueObject::fromArray($variables['image'])->toRenderArray();
  }
}
