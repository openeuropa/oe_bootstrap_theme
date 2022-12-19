<?php

/**
 * @file
 * Functions to support OpenEuropa Bootstrap theme settings.
 */

declare(strict_types = 1);

use Drupal\Core\Form\FormStateInterface;

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
}
