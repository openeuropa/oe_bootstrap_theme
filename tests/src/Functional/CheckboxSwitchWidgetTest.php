<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Tests the checkbox switch third party settings on compatible widgets.
 */
class CheckboxSwitchWidgetTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'entity_test',
    'field_ui',
    'options',
    'oe_bootstrap_theme_helper',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'oe_bootstrap_theme';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Add a field for each type of supported widget.
    $storage = FieldStorageConfig::create([
      'field_name' => 'field_boolean',
      'entity_type' => 'entity_test',
      'type' => 'boolean',
    ]);
    $storage->save();
    FieldConfig::create([
      'field_storage' => $storage,
      'bundle' => 'entity_test',
    ])->save();
    $storage = FieldStorageConfig::create([
      'field_name' => 'field_options',
      'entity_type' => 'entity_test',
      'type' => 'list_string',
      'cardinality' => -1,
      'settings' => [
        'allowed_values' => [
          'yes' => 'yes',
          'no' => 'no',
        ],
      ],
    ]);
    $storage->save();
    FieldConfig::create([
      'field_storage' => $storage,
      'bundle' => 'entity_test',
    ])->save();

    /** @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface $display_repository */
    $display_repository = \Drupal::service('entity_display.repository');

    // Create a form display for the default form mode.
    $display_repository->getFormDisplay('entity_test', 'entity_test')
      ->setComponent('field_boolean', [
        'type' => 'boolean_checkbox',
      ])
      ->setComponent('field_options', [
        'type' => 'options_buttons',
      ])
      ->save();
  }

  /**
   * Tests the widget settings and rendering.
   */
  public function testWidgetSettings(): void {
    $entity = EntityTest::create([
      'name' => 'Test entity',
    ]);
    $entity->save();

    $this->drupalLogin($this->drupalCreateUser([
      'access administration pages',
      'view test entity',
      'administer entity_test content',
      'administer entity_test fields',
      'administer entity_test form display',
    ]));

    $this->drupalGet($entity->toUrl('edit-form'));
    $assert_session = $this->assertSession();
    // The ".form-switch" class identifies a checkbox that will be rendered as
    // switch by BCL.
    $assert_session->elementsCount('css', '.form-check.form-item-field-boolean-value', 1);
    $assert_session->elementsCount('css', '.form-switch.form-check.form-item-field-boolean-value', 0);
    $assert_session->elementsCount('css', '.form-check.form-item-field-options-yes', 1);
    $assert_session->elementsCount('css', '.form-switch.form-check.form-item-field-options-yes', 0);
    $assert_session->elementsCount('css', '.form-check.form-item-field-options-no', 1);
    $assert_session->elementsCount('css', '.form-switch.form-check.form-item-field-options-no', 0);

    // Visit the form display configuration page.
    $this->drupalGet('entity_test/structure/entity_test/form-display');
    // The two widgets start with the switch setting turned off.
    $single_field_row = $assert_session->elementExists('css', '[data-drupal-selector="edit-fields-field-boolean"]');
    $this->assertStringContainsStringIgnoringCase('Switch: No', $single_field_row->getText());
    $multiple_field_row = $assert_session->elementExists('css', '[data-drupal-selector="edit-fields-field-options"]');
    $this->assertStringContainsStringIgnoringCase('Switches: No', $multiple_field_row->getText());

    // Enable the switch for the boolean field.
    $single_field_row->pressButton('field_boolean_settings_edit');
    $single_field_row->checkField('Switch');
    $single_field_row->pressButton('Update');
    $assert_session->buttonExists('Save')->press();
    $this->assertStringContainsStringIgnoringCase('Switch: Yes', $single_field_row->getText());
    $this->assertStringContainsStringIgnoringCase('Switches: No', $multiple_field_row->getText());

    $this->drupalGet($entity->toUrl('edit-form'));
    $assert_session->elementsCount('css', '.form-switch.form-check.form-item-field-boolean-value', 1);
    $assert_session->elementsCount('css', '.form-check.form-item-field-options-yes', 1);
    $assert_session->elementsCount('css', '.form-switch.form-check.form-item-field-options-yes', 0);
    $assert_session->elementsCount('css', '.form-check.form-item-field-options-no', 1);
    $assert_session->elementsCount('css', '.form-switch.form-check.form-item-field-options-no', 0);

    // Enable the switch for the list field.
    $this->drupalGet('entity_test/structure/entity_test/form-display');
    $multiple_field_row->pressButton('field_options_settings_edit');
    $multiple_field_row->checkField('Switches');
    $multiple_field_row->pressButton('Update');
    $assert_session->buttonExists('Save')->press();
    $this->assertStringContainsStringIgnoringCase('Switches: Yes', $multiple_field_row->getText());
    $this->assertStringContainsStringIgnoringCase('Switch: Yes', $single_field_row->getText());

    $this->drupalGet($entity->toUrl('edit-form'));
    $assert_session->elementsCount('css', '.form-switch.form-check.form-item-field-boolean-value', 1);
    $assert_session->elementsCount('css', '.form-switch.form-check.form-item-field-options-yes', 1);
    $assert_session->elementsCount('css', '.form-switch.form-check.form-item-field-options-no', 1);
  }

}
