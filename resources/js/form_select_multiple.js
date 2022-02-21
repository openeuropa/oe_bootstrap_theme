/**
 * @file
 * Apply slim Select library to all form select multiple.
 */

(function (Drupal) {
  Drupal.behaviors.myBehavior = {
    attach: function (context, settings) {
      new SlimSelect({
        select: '.multi-select',
      });
    }
  }
} (Drupal));
