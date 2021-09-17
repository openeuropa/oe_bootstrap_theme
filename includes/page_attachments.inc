<?php

/**
 * @file
 * Custom page attachments hooks.
 */

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Html;
use Drupal\Core\Render\Markup;

/**
 * Implements hook_page_attachments_alter().
 */
function oe_bootstrap_theme_page_attachments_alter(array &$attachments) {
  global $base_url;
  $theme = \Drupal::theme()->getActiveTheme();
  $theme_path = $base_url . '/' . $theme->getPath();
  $theme_path_safe = Html::escape($theme_path);
  $markup = '<link rel="stylesheet" href="' . $theme_path_safe . '/assets/css/bootstrap-ie11.css">'
    . '<script src="' . $theme_path_safe . '/assets/js/bootstrap-5.0.0-beta2-ie11.js"></script>'
    . '<script src="' . $theme_path_safe . '/assets/js/polyfill-ie11.min.js"></script>';

  // Bootstrap-ie11 inline JS which will load the CSS and JS just for IE users.
  // https://github.com/coliff/bootstrap-ie11
  $script = 'window.MSInputMethodContext && document.documentMode && document.write(' . Json::encode($markup) . ');';

  // Add JS to html_head without escaping it:
  $attachments['#attached']['html_head'][] = [
    [
      '#type' => 'html_tag',
      '#tag' => 'script',
      '#attributes' => [
        'type' => 'text/javascript',
      ],
      '#weight' => 100,
      '#value' => Markup::create($script),
    ],
    'bootstrapIe11',
  ];

}
