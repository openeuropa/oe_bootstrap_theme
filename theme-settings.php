<?php

/**
 * @file
 * Functions to support OpenEuropa Bootstrap theme settings.
 */

declare(strict_types=1);

use Drupal\Core\Form\FormStateInterface;
use Drupal\oe_bootstrap_theme\BackwardCompatibility;

/**
 * Implements hook_form_system_theme_settings_alter().
 */
function oe_bootstrap_theme_form_system_theme_settings_alter(&$form, FormStateInterface $form_state) {
  $form['bootstrap_tables'] = [
    '#type' => 'details',
    '#title' => t('Tables'),
    '#open' => TRUE,
    '#tree' => TRUE,
  ];

  $form['bootstrap_tables']['enable'] = [
    '#type' => 'checkbox',
    '#title' => t('Style all tables using Bootstrap.'),
    '#description' => t('Applies Bootstrap classes to all tables rendered using this theme. Note that some table instances (such as calendars) might not render correctly when Bootstrap is applied to them.'),
    '#default_value' => theme_get_setting('bootstrap_tables.enable'),
  ];

  $form['bootstrap_tables']['responsive'] = [
    '#type' => 'select',
    '#title' => t('Responsive'),
    '#description' => t('Make the tables responsive starting from the specified breakpoint. Choose "always" or "never" to turn on or off for all breakpoints.'),
    '#options' => [
      'always' => t('Always'),
      'sm' => t('Small'),
      'md' => t('Medium'),
      'lg' => t('Large'),
      'xl' => t('Extra large'),
      'xxl' => t('Extra extra large'),
    ],
    '#empty_option' => t('Never'),
    '#default_value' => theme_get_setting('bootstrap_tables.responsive') ?? '',
  ];

  $form['backward_compatibility'] = [
    '#type' => 'details',
    '#title' => t('Backward compatibility'),
    '#description' => t('The following settings allow to keep the backward compatibility with previous releases of <em>oe_bootstrap_theme</em>. When turning them off, please check that your code is updated accordingly. All the settings introduced since the theme installation will be on by default.'),
    '#open' => TRUE,
    '#tree' => TRUE,
  ];

  $form['backward_compatibility']['card_search_image_hide_on_mobile'] = [
    '#type' => 'checkbox',
    '#title' => t('Card image hidden on mobile'),
    '#description' => t('The search variant of the card pattern did not show image on mobile.'),
    '#default_value' => BackwardCompatibility::getSetting('card_search_image_hide_on_mobile'),
  ];

  $form['backward_compatibility']['card_search_use_grid_classes'] = [
    '#type' => 'checkbox',
    '#title' => t('Card to use grid classes'),
    '#description' => t('Card search variant used grid classes to structure its content left-right, this changed to col-12 and cl-card-start-col combination, this has an impact on the column sizes.'),
    '#default_value' => BackwardCompatibility::getSetting('card_search_use_grid_classes'),
  ];

  $form['backward_compatibility']['fieldset_wrapper_col_sm_10'] = [
    '#type' => 'checkbox',
    '#title' => t('Fieldset wrapper uses col-sm-10'),
    '#description' => t('Fieldset wrapper had a class col-sm-10 which caused a different width for its content compared to the rest of the elements.'),
    '#default_value' => BackwardCompatibility::getSetting('fieldset_wrapper_col_sm_10'),
  ];

  $form['backward_compatibility']['featured_media_subtitle_tag_h5'] = [
    '#type' => 'checkbox',
    '#title' => t('Use h5 for subtitle tags in featured media'),
    '#description' => t('Sets subtitle tags to h5 for backward compatibility in featured media.'),
    '#default_value' => BackwardCompatibility::getSetting('featured_media_subtitle_tag_h5'),
  ];
}
