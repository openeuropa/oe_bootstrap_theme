<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel;

use Drupal\Core\Config\FileStorage;
use Drupal\Core\Database\Database;
use Drupal\Tests\oe_bootstrap_theme\Kernel\Traits\RenderTrait;
use Drupal\views\Tests\ViewTestData;

/**
 * Tests rendering of the pager.
 */
class ViewsTableTest extends AbstractKernelTestBase {

  use RenderTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'views',
    'views_test_config',
    'views_test_data',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Configuration and schemas needed for view test.
    $this->installConfig(['system']);

    // Define the schema and views data variable before enabling the module.
    /** @var \Drupal\Core\State\StateInterface $state */
    $state = $this->container->get('state');
    $state->set('views_test_data_schema', ViewTestData::schemaDefinition());
    $state->set('views_test_data_views_data', ViewTestData::viewsData());
    $this->container->get('views.views_data')->clear();
    $this->installConfig(['views', 'views_test_config', 'views_test_data']);
    foreach (ViewTestData::schemaDefinition() as $table => $schema) {
      $this->installSchema('views_test_data', $table);
    }

    // Load the test dataset.
    $data_set = ViewTestData::dataSet();
    $query = Database::getConnection()->insert('views_test_data')
      ->fields(array_keys($data_set[0]));
    foreach ($data_set as $record) {
      $query->values($record);
    }
    $query->execute();

    // Enable the test table view.
    $storage = \Drupal::entityTypeManager()->getStorage('view');
    $config_dir = \Drupal::service('extension.list.module')->getPath('views_test_config') . '/test_views';
    $file_storage = new FileStorage($config_dir);
    $storage->create($file_storage->read('views.view.test_table'))->save();

    // Enable the theme.
    $this->container->get('theme_installer')->install(['oe_bootstrap_theme']);
    $this->config('system.theme')->set('default', 'oe_bootstrap_theme')->save();
    $this->container->set('theme.registry', NULL);
  }

  /**
   * Tests a table view classes.
   */
  public function testTableViewClasses(): void {
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = \Drupal::configFactory();
    $theme_settings = $config_factory->getEditable('oe_bootstrap_theme.settings');

    // Check default classes, tables enabled and always responsive.
    $this->assertRendering(
      $this->getViewTableHtml(),
      [
        'count' => [
          'table.table.table-responsive' => 1,
          '.table' => 1,
          '.table-responsive' => 1,
        ],
      ]
    );

    // Disable tables and check that no classes are added.
    $theme_settings->set('bootstrap_tables.enable', FALSE)->save();
    $this->assertRendering(
      $this->getViewTableHtml(),
      [
        'count' => [
          'table' => 1,
          '.table' => 0,
          '.table-responsive' => 0,
        ],
      ]
    );

    // Set a responsive breakpoint with tables still disabled, no clases.
    $theme_settings->set('bootstrap_tables.responsive', 'lg')->save();
    $this->assertRendering(
      $this->getViewTableHtml(),
      [
        'count' => [
          'table' => 1,
          '.table' => 0,
          '.table-responsive-lg' => 0,
          '.table-responsive' => 0,
        ],
      ]
    );

    // Enable tables with previous breakpoint, now both are applied.
    $theme_settings->set('bootstrap_tables.enable', TRUE)->save();
    $this->assertRendering(
      $this->getViewTableHtml(),
      [
        'count' => [
          'table.table.table-responsive-lg' => 1,
          '.table' => 1,
          '.table-responsive-lg' => 1,
          '.table-responsive' => 0,
        ],
      ]
    );

    // Change tables responsive breakpoint.
    $theme_settings->set('bootstrap_tables.responsive', 'md')->save();
    $this->assertRendering(
      $this->getViewTableHtml(),
      [
        'count' => [
          'table.table.table-responsive-md' => 1,
          '.table' => 1,
          '.table-responsive-md' => 1,
          '.table-responsive-lg' => 0,
          '.table-responsive' => 0,
        ],
      ]
    );

    // Disable responsive, only Bootstrap table class is present.
    $theme_settings->set('bootstrap_tables.responsive', '')->save();
    $this->assertRendering(
      $this->getViewTableHtml(),
      [
        'count' => [
          'table.table' => 1,
          '.table' => 1,
          '.table-responsive-md' => 0,
          '.table-responsive' => 0,
        ],
      ]
    );
  }

  /**
   * Gets HTML from rendering 'test_table' view.
   *
   * @return string
   *   The resulting HTML.
   */
  protected function getViewTableHtml(): string {
    $build = [
      '#type' => 'view',
      '#name' => 'test_table',
      '#display_id' => 'default',
      '#arguments' => [],
    ];
    /** @var \Drupal\Core\Render\RendererInterface $renderer */
    $renderer = \Drupal::service('renderer');

    // We clear theme setting static cache always before rendering,
    // configuration may have changed.
    drupal_static_reset('theme_get_setting');

    return (string) $renderer->renderRoot($build);
  }

}
