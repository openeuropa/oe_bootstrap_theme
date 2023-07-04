<?php

/**
 * @file
 * Functions to support OpenEuropa Bootstrap theme settings.
 */

declare(strict_types = 1);

use Drupal\Core\Form\FormStateInterface;
use Drupal\oe_bootstrap_theme\BackwardsCompatibility;

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

  $form['oebt_bc'] = [
    '#type' => 'details',
    '#title' => t('Backwards compatibility'),
    '#open' => TRUE,
    '#tree' => TRUE,
  ];

  $form['oebt_bc']['card_search_image_not_visible_on_mobile'] = [
    '#type' => 'checkbox',
    '#title' => t('Card image not visible on mobile'),
    '#description' => t('Card search variant did not show image on mobile, check this to keep the behaviour.'),
    '#default_value' => BackwardsCompatibility::getSetting('card_search_image_not_visible_on_mobile'),
  ];

  $form['oebt_bc']['card_search_use_grid_classes'] = [
    '#type' => 'checkbox',
    '#title' => t('Card to use grid classes'),
    '#description' => t('Card search variant used grid classes to structure its content left-right, this changed to col-12 and cl-card-start-col combination, this has an impact on the column sizes. Check this to keep the old behaviour.'),
    '#default_value' => BackwardsCompatibility::getSetting('card_search_use_grid_classes'),
  ];
}
