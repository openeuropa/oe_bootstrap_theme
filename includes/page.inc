<?php

/**
 * @file
 * Theme and preprocess functions for page.
 */

declare(strict_types = 1);

/**
 * Implements hook_preprocess_HOOK() for page templates.
 */
function oe_bootstrap_theme_preprocess_page(&$variables) {
  // @todo It would be better to have a setting, like bootstrap_barrio do with
  //   bootstrap_barrio_fluid_container ('container-fluid' : 'container')
  $variables['container'] = 'container';
}
