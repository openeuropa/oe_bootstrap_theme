<?php

/**
 * @file
 * Theme and preprocess functions for html.
 */

/**
 * Implements hook_preprocess_html().
 */
function oe_bootstrap_theme_preprocess_html(array &$variables) {
  // Add "user-logged-in" class to body for Behat to detect whether the current
  // user is logged in or not.
  if (!empty($variables['logged_in'])) {
    $variables['attributes']['class'][] = 'user-logged-in';
  }
}
