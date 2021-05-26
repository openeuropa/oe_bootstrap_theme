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
function eua_theme_page_attachments_alter(array &$attachments) {
  global $base_url;
  $theme = \Drupal::theme()->getActiveTheme();
  $theme_path = $base_url.'/'. $theme->getPath();
  $theme_path_safe = Html::escape($theme_path);
  $markup = ''
    . '<link rel="stylesheet" href="' . $theme_path_safe . '/assets/css/bootstrap-ie11.css">'
    . '<script src="https://cdn.jsdelivr.net/combine/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js,npm/ie11-custom-properties@4,npm/element-qsa-scope@1"><\/script>'
    . '<script crossorigin="anonymous" src="https://polyfill.io/v3/polyfill.min.js?features=default%2CNumber.parseInt%2CNumber.parseFloat%2CArray.prototype.find%2CArray.prototype.includes"><\/script>'
    . '<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>'
    . '<script>!function () { var e, t; ((e = document.createEvent("CustomEvent")).initEvent("Bootstrap", !0, !0), e.preventDefault(), e.defaultPrevented) || (t = Event.prototype.preventDefault, Event.prototype.preventDefault = function () { this.cancelable && (t.call(this), Object.defineProperty(this, "defaultPrevented", { get: function () { return !0 }, configurable: !0 })) }) }();</script>';

  // Bootstrap-ie11 inline JS which will load the CSS and JS - just for IE users.
  // https://github.com/coliff/bootstrap-ie11
  $script = 'window.MSInputMethodContext && document.documentMode && document.write(' . Json::encode($markup) . ');';

  // Add JS to html_head without escaping it:
  // https://www.drupal.org/project/drupal/issues/2752283#comment-12622807
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
