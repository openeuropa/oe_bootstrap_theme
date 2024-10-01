<?php

/**
 * @file
 * Theme and preprocess functions for patterns.
 */

declare(strict_types=1);

use Drupal\Component\Utility\Html;
use Drupal\Core\Render\Markup;
use Drupal\oe_bootstrap_theme\BackwardCompatibility;
use Drupal\oe_bootstrap_theme\ValueObject\FileValueObject;
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
  if (is_array($variables['image']) && !empty($variables['image'])) {
    $variables['image'] = ImageValueObject::fromArray($variables['image']);
  }

  if ($variables['image'] instanceof ImageValueObjectInterface) {
    // Transform an object implementing ImageValueObjectInterface to our own
    // implementation, so that we can use the specific method.
    $variables['image'] = ImageValueObject::fromArray($variables['image']->getArray())
      ->toRenderArray();
  }

  $variables['featured_media_subtitle_tag_h5'] = BackwardCompatibility::getSetting('featured_media_subtitle_tag_h5');
}

/**
 * Implements hook_preprocess_HOOK() for inpage navigation pattern.
 */
function oe_bootstrap_theme_preprocess_pattern_inpage_navigation(&$variables): void {
  $variables['inpage_navigation_dropdown_id'] = Html::getUniqueId(($variables['attributes']['id'] ?? 'bcl-inpage-navigation') . '-dropdown');
  $variables['inpage_navigation_id'] = $variables['attributes']['id'] ?? Html::getUniqueId('bcl-inpage-navigation');
}

/**
 * Implements hook_preprocess_HOOK().
 */
function oe_bootstrap_theme_preprocess_pattern_fact_figures(array &$variables): void {
  foreach ($variables['items'] as &$item) {
    // If the icon is not an array, the icon name has been passed.
    if (!is_array($item['icon'])) {
      $item['icon'] = [
        'name' => $item['icon'],
      ];
    }
  }
}

/**
 * Implements hook_preprocess_HOOK() for file pattern preview.
 */
function oe_bootstrap_theme_preprocess_pattern_file__preview(array &$variables): void {
  $variables['file'] = FileValueObject::fromArray($variables['file']);

  foreach ($variables['translations'] as $index => $translation) {
    $variables['translations'][$index] = FileValueObject::fromArray($translation);
  }
}

/**
 * Implements hook_preprocess_HOOK() for card pattern preview.
 */
function oe_bootstrap_theme_preprocess_pattern_card__preview(array &$variables): void {
  if (isset($variables['image'])) {
    $variables['image'] = ImageValueObject::fromArray($variables['image']);
  }
}

/**
 * Implements hook_preprocess_HOOK() for card_layout pattern preview.
 */
function oe_bootstrap_theme_preprocess_pattern_card_layout__preview(array &$variables): void {
  foreach ($variables['items'] as &$item) {
    if (isset($item['image'])) {
      $item['image'] = ImageValueObject::fromArray($item['image']);
    }
  }
}

/**
 * Implements hook_preprocess_HOOK() for listing pattern preview.
 */
function oe_bootstrap_theme_preprocess_pattern_listing__preview(array &$variables): void {
  foreach ($variables['items'] as &$item) {
    if (isset($item['image'])) {
      $item['image'] = ImageValueObject::fromArray($item['image']);
    }
  }
}

/**
 * Implements hook_preprocess_HOOK() for the pagination pattern.
 *
 * Adds default values for custom icons.
 */
function oe_bootstrap_theme_preprocess_pattern_pagination(array &$variables): void {
  foreach (['first', 'prev', 'next', 'last'] as $key) {
    if (empty($variables[$key]['icon']['name'])) {
      continue;
    }
    $variables[$key]['icon'] += [
      'path' => $variables['bcl_icon_path'],
      'size' => 'fluid',
    ];
  }
}

/**
 * Implements hook_preprocess_HOOK() for pagination pattern preview.
 */
function oe_bootstrap_theme_preprocess_pattern_pagination__preview(array &$variables): void {
  if ($variables['variant'] !== 'glossary') {
    return;
  }

  // Remove entries which don't make sense for a glossary.
  foreach (['first', 'prev', 'next', 'last'] as $key) {
    unset($variables[$key]);
  }

  // Create one entry per letter A-Z and number 0-9.
  $variables['items'] = array_map(function (string $char) {
    return [
      'path' => '#',
      'label' => $char,
    ];
  }, array_merge(range('A', 'Z'), range(0, 9)));

  // Mark the letter F as active.
  $variables['items'][5]['active'] = TRUE;
}

/**
 * Implements hook_preprocess_HOOK() for gallery pattern.
 */
function oe_bootstrap_theme_preprocess_pattern_gallery(array &$variables): void {
  $variables['id'] = Html::getUniqueId('bcl-gallery');
}

/**
 * Implements hook_preprocess_HOOK() for gallery pattern preview.
 */
function oe_bootstrap_theme_preprocess_pattern_gallery__preview(array &$variables): void {
  // When #markup is a string, only safe tags will be kept for rendering.
  // Convert to a markup object to avoid any filtering.
  // @see \Drupal\Core\Render\Renderer::ensureMarkupIsSafe()
  foreach ($variables['items'] as &$item) {
    foreach (['thumbnail', 'media'] as $key) {
      if (isset($item[$key]['#markup'])) {
        $item[$key] = Markup::create($item[$key]['#markup']);
      }
    }
  }
}

/**
 * Implements hook_preprocess_HOOK() for gallery pattern.
 */
function oe_bootstrap_theme_preprocess_pattern_card(array &$variables): void {
  $variables['bc_card_search_image_hide_on_mobile'] = BackwardCompatibility::getSetting('card_search_image_hide_on_mobile');
  $variables['bc_card_search_use_grid_classes'] = BackwardCompatibility::getSetting('card_search_use_grid_classes');
}
