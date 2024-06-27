<?php

/**
 * @file
 * Custom page attachments hooks.
 */

declare(strict_types=1);

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Html;
use Drupal\Core\Render\Markup;

/**
 * Implements hook_page_attachments_alter().
 */
function oe_bootstrap_theme_page_attachments_alter(array &$attachments) {
  global $base_url;
  $theme_handler = \Drupal::service('theme_handler');
  $theme_path = $base_url . '/' . $theme_handler->getTheme('oe_bootstrap_theme')->getPath();
  $theme_path_safe = Html::escape($theme_path);
  $markup = '<link rel="stylesheet" href="' . $theme_path_safe . '/assets/css/bootstrap-ie11.css">'
    . '<script src="https://cdn.jsdelivr.net/combine/npm/ie11-custom-properties@4,npm/element-qsa-scope@1"></script>'
    . '<script crossorigin="anonymous" src="https://cdnjs.cloudflare.com/polyfill/v3/polyfill.min.js?version=3.111.0&features=Array.prototype.find%2CArray.prototype.includes%2CNumber.parseFloat%2CNumber.parseInt"></script>';

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
