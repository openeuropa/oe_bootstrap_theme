<?php

declare (strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel\Paragraphs;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\filter\Entity\FilterFormat;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the rendering for timeline paragraph type.
 */
class TimelineParagraphsTest extends ParagraphsTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'oe_content_timeline_field',
    'oe_paragraphs_timeline',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('node');
    $this->installConfig(['field', 'node', 'system', 'oe_paragraphs_timeline']);
    $this->create();

    $this->container->get('theme_installer')->install(['oe_bootstrap_theme']);
    $this->config('system.theme')->set('default', 'oe_bootstrap_theme')->save();
    $this->container->set('theme.registry', NULL);
  }

  /**
   * Data provider for testTimeline() and testTimelineRender().
   *
   * @return array
   *   The test data.
   */
  public function timelineDataProvider(): array {
    $data = [];

    $data[] = [
      'values' => [
        [
          'label' => '13 September 2017',
          'title' => 'item title 1',
          'body' => '<a href="/example">President Juncker\'s State of the Union speech</a>',
          'format' => 'my_text_format',
        ],
        [
          'label' => '28-29 September 2017',
          'title' => 'item title 2',
          'body' => '<a href="/example">Informal Digital Summit, Tallinn</a>',
          'format' => 'my_text_format',
        ],
        [
          'label' => '14 November 2017',
          'title' => 'item title 3',
          'body' => 'Strengthening European identity through education and culture: European Commission\'s contribution to the Leaders\' meeting.',
          'format' => 'my_text_format',
        ],
        [
          'label' => '17 November 2017',
          'title' => 'item title 4',
          'body' => '<a href="/example">Social Summit in Gothenburg, Sweden</a>',
          'format' => 'my_text_format',
        ],
        [
          'label' => '6 December 2017',
          'title' => 'item title 5',
          'body' => '<a href="/example">Economic and Monetary Union package of proposals</a>',
          'format' => 'my_text_format',
        ],
        [
          'label' => '14-15 December 2017',
          'title' => 'item title 6',
          'body' => '<a href="/example">EU Leaders\' meeting on migration, Brussels</a>',
          'format' => 'my_text_format',
        ],
        [
          'label' => '15 December 2017',
          'title' => 'item title 7',
          'body' => '<a href="/example">Euro Summit</a>',
          'format' => 'my_text_format',
        ],
        [
          'label' => '6 February 2018',
          'title' => 'item title 8',
          'body' => '<a href="/example">EU-Western Balkans Strategy</a>',
          'format' => 'my_text_format',
        ],
        [
          'label' => '14 February 2018',
          'title' => 'item title 9',
          'body' => '<a href="/example">Multiannual Financial Framework</a> and <a href="/example">institutional issues</a> - enhancing efficiency at the helm of the European Union',
          'format' => 'my_text_format',
        ],
      ],
      'button_values' => [
        'show_items' => 'Show more 4 items',
        'hide_items' => 'Hide 4 items',
      ],
      'title_values' => [
        '13 September 2017',
        '28-29 September 2017',
        '14 November 2017',
        '17 November 2017',
        '6 December 2017',
        '14-15 December 2017',
        '15 December 2017',
        '6 February 2018',
        '14 February 2018',
      ],
      'content_values' => [
        'President Juncker\'s State of the Union speech',
        'Informal Digital Summit, Tallinn',
        'Social Summit in Gothenburg, Sweden',
        'Economic and Monetary Union package of proposals',
        'EU Leaders\' meeting on migration, Brussels',
        'Euro Summit',
        'EU-Western Balkans Strategy',
        'Multiannual Financial Framework',
        'institutional issues',
      ],
    ];
    return $data;
  }

  /**
   * Test the timeline node field.
   *
   * @param array $values
   *   The expected to test.
   *
   * @dataProvider timelineDataProvider
   */
  public function testTimeline(array $values): void {
    // Node field timeline values.
    $node_values = [
      'type' => 'test_ct',
      'title' => 'My node title',
      'field_timeline' => $values,
    ];

    // Create node.
    $node = Node::create($node_values);
    $node->save();
    $entity_type_manager = \Drupal::entityTypeManager()->getStorage('node');
    $entity_type_manager->resetCache();
    $node->get('field_timeline')->getValue();

    // Assert the base field values.
    $this->assertEquals('My node title', $node->label());
    $this->assertEquals($values, $node->get('field_timeline')->getValue());
  }

  /**
   * Test the timeline paragraph render.
   *
   * @param array $values
   *   The expected to test.
   * @param array $button
   *   The expected values for buttons.
   * @param array $titles
   *   The expected titles.
   * @param array $content
   *   The expected content.
   *
   * @dataProvider timelineDataProvider
   */
  public function testTimelineRender(array $values, array $button, array $titles, array $content): void {
    // Paragraph timeline values.
    $paragraph_storage = $this->container->get('entity_type.manager')->getStorage('paragraph');
    $paragraph = $paragraph_storage->create([
      'type' => 'oe_timeline',
      'field_oe_title' => 'Timeline',
      'field_oe_timeline' => $values,
      'hide_from' => 4,
    ]);
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    // Asserting values.
    $this->assertCount(9, $crawler->filter('h5'));
    $this->assertCount(1, $crawler->filter('button'));
    $this->assertStringContainsString('#chevron-down', trim($crawler->filter('use')->attr('xlink:href')));
    $more_items_button = $crawler->filter('.label-collapsed')->text();
    $this->assertSame($button['show_items'], $more_items_button);
    $hide_items_button = $crawler->filter('.label-expanded')->text();
    $this->assertSame($button['hide_items'], $hide_items_button);
    $paragraph_titles = $crawler->filter('h5')->extract(['_text']);
    $this->assertSame($titles, $paragraph_titles);
    $paragraph_content = $crawler->filter('a')->extract(['_text']);
    $this->assertSame($content, $paragraph_content);
  }

  /**
   * Create content type, field format and timeline field.
   */
  protected function create(): void {

    // Display options for formatter.
    $displayOptions = [
      'type' => 'timeline_formatter',
      'label' => 'hidden',
      'settings' => [
        'limit' => 2,
        'show_more' => 'Button label',
      ],
    ];

    // Create content type.
    $type = NodeType::create([
      'name' => 'Test content type',
      'type' => 'test_ct',
    ]);
    $type->save();

    FilterFormat::create([
      'format' => 'my_text_format',
      'name' => 'My text format',
      'filters' => [
        'filter_autop' => [
          'module' => 'filter',
          'status' => TRUE,
        ],
      ],
    ])->save();

    $fieldStorage = FieldStorageConfig::create([
      'field_name' => 'field_timeline',
      'entity_type' => 'node',
      'type' => 'timeline_field',
      'cardinality' => -1,
      'entity_types' => ['node'],
    ]);
    $fieldStorage->save();

    $field = FieldConfig::create([
      'label' => 'Timeline field',
      'field_name' => 'field_timeline',
      'entity_type' => 'node',
      'bundle' => 'test_ct',
      'settings' => [],
      'required' => FALSE,
    ]);
    $field->save();

    EntityViewDisplay::create([
      'targetEntityType' => $field->getTargetEntityTypeId(),
      'bundle' => $field->getTargetBundle(),
      'mode' => 'default',
      'status' => TRUE,
    ])->setComponent($fieldStorage->getName(), $displayOptions)
      ->save();
  }

}
