/**
 * @file
 * Apply slim Select library to all form select multiple.
 */

(function (Drupal) {
  Drupal.behaviors.myBehavior = {
    attach: function (context, settings) {
      if (document.getElementsByClassName('multi-select').length > 0) {
        new SlimSelect({
          select: '.multi-select',
        });
      }
    }
  }
} (Drupal));
