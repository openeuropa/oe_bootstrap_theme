<?php

/**
 * @file
 * The OE Boostrap Theme settings.
 */

declare(strict_types = 1);

use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_form_FORM_ID_alter()
 */
function oe_bootstrap_theme_form_system_theme_settings_alter(array &$form, FormStateInterface $form_state, ?string $form_id = NULL): void {
  // Work-around for a core bug affecting admin themes. See issue #943212.
  if (isset($form_id)) {
    return;
  }

  $form['slimselect'] = [
    '#type' => 'details',
    '#tree' => TRUE,
    '#title' => t('Slim Select settings'),
    'enabled' => [
      '#type' => 'checkbox',
      '#title' => t('Enable Slim Select on all select form elements'),
      '#default_value' => theme_get_setting('slimselect.enabled'),
    ],
  ];
}
