<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme_fences\Kernel;

use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Tests\oe_bootstrap_theme\Kernel\AbstractKernelTestBase;

/**
 * Tests the field templates with the Fences settings.
 *
 * @group oe_bootstrap_theme
 */
class FieldTemplateTest extends AbstractKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'entity_test',
    'field',
    'fences',
    'oe_bootstrap_theme_fences',
  ];

  /**
   * The test entity.
   *
   * @var \Drupal\entity_test\Entity\EntityTest
   */
  protected EntityTest $entity;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('entity_test');
    $this->installSchema('user', ['users_data']);
    FieldStorageConfig::create([
      'field_name' => 'field_test',
      'entity_type' => 'entity_test',
      'type' => 'string',
      'cardinality' => -1,
    ])->save();
    FieldConfig::create([
      'field_name' => 'field_test',
      'entity_type' => 'entity_test',
      'bundle' => 'entity_test',
      'label' => 'Test field',
    ])->save();

    $this->entity = EntityTest::create([
      'field_test' => ['First value', 'Second value'],
    ]);
    $this->entity->save();
  }

  /**
   * Tests that the module templates replace the base theme ones.
   */
  public function testThemeRegistry(): void {
    $registry = $this->container->get('theme.registry')->get();
    $module_path = $this->container->get('extension.list.module')->getPath('oe_bootstrap_theme_fences');

    $this->assertSame($module_path . '/templates', $registry['field']['path']);
    $this->assertSame($module_path . '/templates', $registry['field__bare']['path']);
  }

  /**
   * Tests that the markup does not change without the Fences settings.
   */
  public function testWithoutFencesSettings(): void {
    $html = $this->renderField('field', []);
    $bare_html = $this->renderField('field__bare', []);

    // Render again with the base theme templates.
    $this->container->get('module_installer')->uninstall(['oe_bootstrap_theme_fences']);
    $this->container->get('theme.registry')->reset();

    $this->assertSame($this->renderField('field', []), $html);
    $this->assertSame($this->renderField('field__bare', []), $bare_html);
    $this->assertRendering($html, [
      'count' => [
        'div.entity-test__field-test > div.field__label.fw-bold' => 1,
        'div.entity-test__field-test > div.field__items > div.field__item' => 2,
      ],
    ]);
  }

  /**
   * Tests the markup with the Fences settings.
   */
  public function testWithFencesSettings(): void {
    $settings = [
      'fences_field_tag' => 'section',
      'fences_field_classes' => 'my-field',
      'fences_field_items_wrapper_tag' => 'ul',
      'fences_field_items_wrapper_classes' => 'my-list',
      'fences_field_item_tag' => 'li',
      'fences_field_item_classes' => 'my-item',
      'fences_label_tag' => 'h3',
      'fences_label_classes' => 'my-label',
    ];
    $expected = [
      'count' => [
        'section.my-field.entity-test__field-test' => 1,
        'section > h3.my-label.field__label.fw-bold' => 1,
        'section > ul.my-list.field__items > li.my-item.field__item' => 2,
        'div' => 0,
      ],
      'equals' => [
        'section > h3' => 'Test field',
      ],
    ];
    $this->assertRendering($this->renderField('field', $settings), $expected);
    $this->assertRendering($this->renderField('field__bare', $settings), $expected);

    // No markup at all, only the label and the values.
    $settings = [
      'fences_field_tag' => 'none',
      'fences_field_items_wrapper_tag' => 'none',
      'fences_field_item_tag' => 'none',
      'fences_label_tag' => 'none',
    ] + $settings;
    $html = $this->renderField('field', $settings);
    $this->assertRendering($html, [
      'count' => [
        'section' => 0,
        'h3' => 0,
        'ul' => 0,
        'li' => 0,
        'div' => 0,
      ],
    ]);
    $this->assertSame('Test field First value Second value', trim(preg_replace('/\s+/', ' ', strip_tags($html))));
  }

  /**
   * Renders the test field.
   *
   * @param string $theme_hook
   *   The theme hook to use.
   * @param array $fences_settings
   *   The Fences settings of the formatter.
   *
   * @return string
   *   The rendered HTML.
   */
  protected function renderField(string $theme_hook, array $fences_settings): string {
    $display_options = [
      'type' => 'string',
      'label' => 'above',
    ];
    if ($fences_settings) {
      $display_options['third_party_settings']['fences'] = $fences_settings;
    }
    $build = $this->entity->get('field_test')->view($display_options);
    $build['#theme'] = $theme_hook;

    return $this->renderRoot($build);
  }

}
