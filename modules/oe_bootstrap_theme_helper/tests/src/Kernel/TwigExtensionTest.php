<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme_helper\Kernel;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Url;
use Drupal\Tests\TestFileCreationTrait;
use Drupal\Tests\image\Kernel\ImageFieldCreationTrait;
use Drupal\Tests\oe_bootstrap_theme\Kernel\AbstractKernelTestBase;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\oe_bootstrap_theme\ValueObject\ImageValueObject;
use Drupal\oe_bootstrap_theme\ValueObject\ImageValueObjectInterface;
use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Component\DomCrawler\Crawler;
use Twig\Markup;

/**
 * Test those Twig extensions that require Drupal to be bootstrapped.
 */
class TwigExtensionTest extends AbstractKernelTestBase {

  use TestFileCreationTrait;
  use ImageFieldCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'entity_test',
    'field',
    'file',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('file');
    $this->installEntitySchema('entity_test');
    $this->installSchema('file', ['file_usage']);
  }

  /**
   * Test bcl_link function.
   */
  public function testBclLinkFunction(): void {
    $items = $this->bclLinkDataProvider();

    foreach ($items as $item) {
      [$expected, $data] = $item;
      $elements = [
        '#type' => 'inline_template',
        '#template' => '{{ bcl_link(label, path, attributes) }}',
        '#context' => [
          'label' => $data['label'],
          'path' => $data['path'],
          'attributes' => new Attribute($data['attributes'] ?? []),
        ],
      ];

      $output = $this->renderRoot($elements);
      $crawler = new Crawler($output);
      $link = $crawler->filter('a');

      $this->assertEquals($expected['href'], $link->attr('href'));
      $this->assertEquals($expected['text'], $link->html());

      if (!empty($expected['attributes'])) {
        foreach ($expected['attributes'] as $name => $value) {
          $this->assertEquals($value, $link->attr($name));
        }
      }
    }
  }

  /**
   * Data provider for bclLinkFunction.
   *
   * @return array
   *   An array of test data arrays with assertions.
   */
  public function bclLinkDataProvider(): array {
    return [
      'string_path_to_front' => [
        [
          'href' => '/',
          'text' => 'My link',
        ],
        [
          'path' => '/',
          'label' => 'My link',
        ],
      ],
      'string_path_front_with_hash' => [
        [
          'href' => '/#kitty',
          'text' => 'Miau',
        ],
        [
          'path' => '/#kitty',
          'label' => 'Miau',
        ],
      ],
      'string_path_current_url_with_hash' => [
        [
          'href' => '#kitty',
          'text' => 'Miau',
        ],
        [
          'path' => '#kitty',
          'label' => 'Miau',
        ],
      ],
      'just_hash' => [
        [
          'href' => '#',
          'text' => 'hash',
        ],
        [
          'path' => '#',
          'label' => 'hash',
        ],
      ],
      'string_path_current_url_with_parameters' => [
        [
          'href' => '?foo=baz',
          'text' => 'My link',
        ],
        [
          'path' => '?foo=baz',
          'label' => 'My link',
        ],
      ],
      'string_path_internal' => [
        [
          'href' => '/some-path',
          'text' => 'Some link',
        ],
        [
          'path' => '/some-path',
          'label' => 'Some link',
        ],
      ],
      'label_with_safe_markup' => [
        [
          'href' => '/',
          'text' => 'This is <em>markup</em>!',
        ],
        [
          'path' => '/',
          // Emulate markup as sent by twig.
          'label' => new Markup('This is <em>markup</em>!', NULL),
        ],
      ],
      'label_with_unsafe_markup' => [
        [
          'href' => '/',
          // Xss filtered markup.
          'text' => 'This is unsafe <em>markup</em>!',
        ],
        [
          'path' => '/',
          'label' => 'This is <script type="text/javascript">unsafe</script> <em>markup</em>!',
        ],
      ],
      'url_object' => [
        [
          'href' => '/node/add',
          'text' => 'Url object link',
        ],
        [
          'path' => Url::fromUserInput('/node/add'),
          'label' => 'Url object link',
        ],
      ],
      'url_object_absolute' => [
        [
          'href' => 'http://localhost/here',
          'text' => 'Absolute url link',
        ],
        [
          'path' => Url::fromUri('base:here', ['absolute' => TRUE]),
          'label' => 'Absolute url link',
        ],
      ],
      'link_with_custom_attribute' => [
        [
          'href' => '/',
          'text' => 'Custom attrib link',
        ],
        [
          'path' => '/',
          'label' => 'Custom attrib link',
          'attributes' => [
            'data-foo' => 'baz',
            'id' => 'id-bar',
          ],
        ],
      ],
      'external_link' => [
        [
          'href' => 'https://www.example.com',
          'text' => 'External link',
        ],
        [
          'path' => 'https://www.example.com',
          'label' => 'External link',
        ],
      ],
      'url with array label' => [
        [
          'href' => 'https://www.example.com/template/',
          'text' => 'user: admin',
        ],
        [
          'path' => 'https://www.example.com/template/',
          'label' => [
            '#type' => 'inline_template',
            '#template' => '{{ prefix }}: {{ suffix }}',
            '#context' => [
              'prefix' => 'user',
              'suffix' => 'admin',
            ],
          ],
        ],
      ],
      'url with array label and url object' => [
        [
          'href' => '/',
          'text' => 'user: anonymous',
        ],
        [
          'path' => Url::fromRoute('<front>'),
          'label' => [
            '#type' => 'inline_template',
            '#template' => '{{ prefix }}: {{ suffix }}',
            '#context' => [
              'prefix' => 'user',
              'suffix' => 'anonymous',
            ],
          ],
        ],
      ],
      'url with array #markup label' => [
        [
          'href' => 'https://www.example.com/markup/',
          'text' => '<b>test</b>',
        ],
        [
          'path' => 'https://www.example.com/markup/',
          'label' => [
            '#markup' => '<b>test</b>',
          ],
        ],
      ],
    ];
  }

  /**
   * Tests the "element_children" Twig filter.
   */
  public function testElementChildrenFilter(): void {
    $required_cache_contexts = $this->container->getParameter('renderer.config')['required_cache_contexts'];
    $renderer = $this->container->get('renderer');

    try {
      foreach ($this->elementChildrenFilterDataProvider() as $scenario => $data) {
        [$items, $expected_output] = $data;
        $expected_bubbled_metadata = [];
        BubbleableMetadata::createFromRenderArray($items)
          ->addCacheContexts($required_cache_contexts)
          ->applyTo($expected_bubbled_metadata);

        $build = [
          '#type' => 'inline_template',
          '#template' => '{{ items|element_children }}',
          '#context' => [
            'items' => $items,
          ],
        ];

        $render_context = new RenderContext();
        $output = $renderer->executeInRenderContext($render_context, function () use ($renderer, $build) {
          return $renderer->render($build, TRUE);
        });

        $bubbled_metadata = [];
        $render_context->pop()->applyTo($bubbled_metadata);
        $this->assertEqualsCanonicalizing($expected_bubbled_metadata, $bubbled_metadata);
        $this->assertEquals($expected_output, $output);
      }
    }
    catch (ExpectationFailedException $e) {
      throw new ExpectationFailedException(sprintf('Failed asserting data for scenario "%s": %s', $scenario, $e->getMessage()), $e->getComparisonFailure(), $e);
    }
    catch (\Exception $e) {
      throw new \Exception(sprintf('Failed asserting data for scenario "%s".', $scenario), 0, $e);
    }
  }

  /**
   * Test the sorting parameter on "element_children" Twig filter.
   */
  public function testElementChildrenFilterSorting(): void {
    $get_build = function ($sort) {
      $template = <<<TWIG
{% for item in items|element_children($sort) %}
  {{- item -}}
{% endfor %}
TWIG;

      return [
        '#type' => 'inline_template',
        '#template' => $template,
        '#context' => [
          'items' => [
            [
              '#plain_text' => 'Weight 20.',
              '#weight' => 20,
            ],
            [
              '#plain_text' => 'Weight -3.',
              '#weight' => -3,
            ],
          ],
        ],
      ];
    };

    $build = $get_build('true');
    $output = $this->renderRoot($build);
    $this->assertEquals('Weight -3.Weight 20.', $output);

    $build = $get_build('false');
    $output = $this->renderRoot($build);
    $this->assertEquals('Weight 20.Weight -3.', $output);
  }

  /**
   * Data provider for testElementChildrenFilter().
   *
   * @return array
   *   The scenarios.
   */
  protected function elementChildrenFilterDataProvider(): array {
    $scenarios = [];

    $basic_build = [
      [
        '#plain_text' => 'Hello.',
      ],
    ];

    $scenarios['with cache tag'] = [
      $basic_build + [
        '#cache' => [
          'tags' => ['test_tag'],
        ],
      ],
      'Hello.',
    ];

    $scenarios['with full cache info'] = [
      $basic_build + [
        '#cache' => [
          'tags' => ['test_tag', 'test_tag_2'],
          'contexts' => ['url'],
          'max-age' => 30,
        ],
      ],
      'Hello.',
    ];

    $scenarios['with attachments'] = [
      $basic_build + [
        '#attached' => [
          'library' => [
            'core/drupal',
            'core/once',
          ],
          'html_head_link' => ['test' => 'head'],
        ],
      ],
      'Hello.',
    ];

    $scenarios['with attachments and cache'] = [
      $basic_build + [
        '#attached' => [
          'library' => [
            'core/drupal',
            'core/once',
          ],
          'html_head_link' => ['test' => 'head'],
        ],
        '#cache' => [
          'contexts' => ['url.path'],
        ],
      ],
      'Hello.',
    ];

    $multiple_items_build = [
      [
        '#plain_text' => 'Item 1.',
      ],
      [
        '#plain_text' => 'Item 2.',
      ],
    ];
    $scenarios['multiple items'] = [
      $multiple_items_build,
      'Item 1.Item 2.',
    ];

    $scenarios['multiple items with metadata'] = [
      $multiple_items_build + [
        '#cache' => [
          'max-age' => CacheBackendInterface::CACHE_PERMANENT,
        ],
      ],
      'Item 1.Item 2.',
    ];

    // Theme wrappers or theme functions are ignored. Only bubbleable metadata
    // is kept.
    $scenarios['with theme wrappers'] = [
      $multiple_items_build + [
        '#theme_wrappers' => ['container'],
      ],
      'Item 1.Item 2.',
    ];

    $scenarios['sorting applied by default'] = [
      [
        [
          '#plain_text' => 'Weight 20.',
          '#weight' => 20,
        ],
        [
          '#plain_text' => 'Weight -3.',
          '#weight' => -3,
        ],
      ],
      'Weight -3.Weight 20.',
    ];

    return $scenarios;
  }

  /**
   * Tests BCL card list filter.
   */
  public function testBclCardList(): void {
    $extension = $this->container->get('oe_bootstrap_theme_helper.twig_extension');
    $data = $this->bclCardListDataProvider();
    $result = $extension->bclCardList($data['items']);

    foreach ($data['expected'] as $key => $expected) {
      $title = $result[$key]['title'] ?? NULL;
      $this->assertEquals($expected['title'], $title ? $title->getText() : '');
      $url = $title ? $title->getUrl() : NULL;

      if (isset($expected['url'])) {
        if ($url->isRouted()) {
          $this->assertEquals($expected['url'], $url->getRouteName());
        }
        else {
          $this->assertEquals($expected['url'], $url->getUri());
        }
        $this->assertEquals($expected['options'], $url->getOptions());
      }

      $this->assertEquals($expected['subtitle'], $result[$key]['subtitle'] ?? []);
      $this->assertEquals($expected['text'], $result[$key]['text'] ?? []);
      $this->assertEquals($expected['image'], $result[$key]['image'] ?? []);
      $this->assertEquals($expected['badges'], $result[$key]['badges'] ?? []);
    }
  }

  /**
   * Provides data for testBclCardList().
   *
   * @return array
   *   The scenarios.
   */
  public function bclCardListDataProvider(): array {
    return [
      'items' => [
        [
          'title' => 'Test title',
          'subtitle' => 'Test subtitle',
          'url' => Url::fromUserInput('/test#something'),
        ],
        [
          'title' => 'Test title',
          'url' => Url::fromRoute('<front>'),
          'text' => 'this is a text',
        ],
        [
          'title' => 'Some title',
          'url' => Url::fromUserInput('/test?foo=1&baz=2'),
        ],
        [
          'image' => ImageValueObject::fromArray([
            'src' => 'http://localhost:8080/web/sites/default/files/testimage.png',
            'name' => 'testimage',
          ]),
          'badges' => [
            'meta 1',
            'meta 2',
          ],
        ],
      ],
      'expected' => [
        [
          'title' => 'Test title',
          'subtitle' => [
            'content' => 'Test subtitle',
            'classes' => 'mb-2',
          ],
          'url' => 'base:test',
          'options' => [
            'attributes' => [
              'class' => 'standalone',
            ],
            'fragment' => 'something',
          ],
          'text' => [],
          'image' => [],
          'badges' => [],
        ],
        [
          'title' => 'Test title',
          'subtitle' => [],
          'url' => '<front>',
          'options' => [
            'attributes' => [
              'class' => 'standalone',
            ],
          ],
          'text' => [
            'content' => 'this is a text',
            'classes' => 'mb-2',
            'tag' => 'div',
          ],
          'image' => [],
          'badges' => [],
        ],
        [
          'title' => 'Some title',
          'subtitle' => [],
          'url' => 'base:test',
          'options' => [
            'attributes' => [
              'class' => 'standalone',
            ],
            'query' => [
              'foo' => 1,
              'baz' => 2,
            ],
          ],
          'text' => [],
          'image' => [],
          'badges' => [],
        ],
        [
          'title' => '',
          'subtitle' => [],
          'text' => [],
          'image' => [
            'path' => 'http://localhost:8080/web/sites/default/files/testimage.png',
            'alt' => '',
          ],
          'badges' => [
            [
              'label' => 'meta 1',
              'background' => 'primary',
            ],
            [
              'label' => 'meta 2',
              'background' => 'primary',
            ],
          ],
        ],
      ],
    ];
  }

  /**
   * Tests image_value_obj returns NULL for empty input.
   */
  public function testImageValueObjEmptyInputReturnsNull(): void {
    $extension = $this->container->get('oe_bootstrap_theme_helper.twig_extension');
    $this->assertNull($this->imageValueObject(NULL));
    $this->assertNull($this->imageValueObject([]));
  }

  /**
   * Tests image_value_obj returns NULL when no image children exist.
   */
  public function testImageValueObjNoImageChildrenReturnsNull(): void {
    FieldStorageConfig::create([
      'entity_type' => 'entity_test',
      'field_name' => 'field_text',
      'type' => 'string',
    ])->save();
    FieldConfig::create([
      'field_storage' => FieldStorageConfig::loadByName('entity_test', 'field_text'),
      'bundle' => 'entity_test',
    ])->save();

    $entity = EntityTest::create([
      'name' => 'no_image',
      'field_text' => 'not an image',
    ]);
    $entity->save();

    $extension = $this->container->get('oe_bootstrap_theme_helper.twig_extension');

    $element = ['child' => ['#markup' => 'no item here']];
    $this->assertNull($this->imageValueObject($element));

    $element = ['child' => ['#item' => $entity->get('field_text')->first()]];
    $this->assertNull($this->imageValueObject($element));
  }

  /**
   * Tests image_value_obj returns a single object.
   */
  public function testImageValueObjSingleImageReturnsSingleObject(): void {
    $this->createImageField('field_image', 'entity_test', 'entity_test');
    $file = $this->createImageFileEntity();

    $entity = EntityTest::create([
      'name' => 'single',
      'field_image' => [
        'target_id' => $file->id(),
        'alt' => 'Single alt',
        'title' => 'Single title',
      ],
    ]);
    $entity->save();

    $extension = $this->container->get('oe_bootstrap_theme_helper.twig_extension');
    $element = ['child' => ['#item' => $entity->get('field_image')->first()]];
    $result = $this->imageValueObject($element);
    $this->assertInstanceOf(ImageValueObjectInterface::class, $result);
    $this->assertEquals('Single title', $result->getName());
    $this->assertEquals('Single alt', $result->getAlt());
    $this->assertStringContainsString('/files/', $result->getSource());
  }

  /**
   * Tests a field item list is reduced to its first item.
   */
  public function testImageValueObjFieldItemListIsReducedFirst(): void {
    $this->createImageField('field_image', 'entity_test', 'entity_test');
    $file = $this->createImageFileEntity();

    $entity = EntityTest::create([
      'name' => 'single',
      'field_image' => [
        'target_id' => $file->id(),
        'alt' => 'Single alt',
        'title' => 'Single title',
      ],
    ]);
    $entity->save();

    $extension = $this->container->get('oe_bootstrap_theme_helper.twig_extension');
    // Pass the whole item list rather than a single item.
    $element = ['child' => ['#item' => $entity->get('field_image')]];
    $result = $this->imageValueObject($element);
    $this->assertInstanceOf(ImageValueObjectInterface::class, $result);
    $this->assertEquals('Single title', $result->getName());
  }

  /**
   * Tests an image style produces a styled source URL.
   */
  public function testImageValueObjImageStyleProducesStyledSource(): void {
    $this->createImageField('field_image', 'entity_test', 'entity_test');
    $file = $this->createImageFileEntity();
    ImageStyle::create(['name' => 'test_style'])->save();

    $entity = EntityTest::create([
      'name' => 'single',
      'field_image' => [
        'target_id' => $file->id(),
        'alt' => 'Single alt',
        'title' => 'Single title',
      ],
    ]);
    $entity->save();

    $extension = $this->container->get('oe_bootstrap_theme_helper.twig_extension');
    $element = [
      'child' => [
        '#item' => $entity->get('field_image')->first(),
        '#image_style' => 'test_style',
      ],
    ];
    $result = $this->imageValueObject($element);
    $this->assertInstanceOf(ImageValueObjectInterface::class, $result);
    $this->assertStringContainsString('/styles/test_style/', $result->getSource());
  }

  /**
   * Tests image_value_obj bubbles cacheability from its value object.
   */
  public function testImageValueObjBubblesCacheability(): void {
    $this->createImageField('field_image', 'entity_test', 'entity_test');
    $file = $this->createImageFileEntity();
    $style = ImageStyle::create(['name' => 'test_style']);
    $style->save();

    $entity = EntityTest::create([
      'name' => 'single',
      'field_image' => [
        'target_id' => $file->id(),
        'alt' => 'Single alt',
        'title' => 'Single title',
      ],
    ]);
    $entity->save();

    $build = [
      '#type' => 'inline_template',
      '#template' => '{% set image = image|image_value_obj %}',
      '#context' => [
        'image' => [
          'child' => [
            '#item' => $entity->get('field_image')->first(),
            '#image_style' => 'test_style',
          ],
        ],
      ],
    ];
    $renderer = $this->container->get('renderer');
    $render_context = new RenderContext();
    $renderer->executeInRenderContext($render_context, function () use ($renderer, $build) {
      $renderer->render($build);
    });

    $bubbled_metadata = [];
    $render_context->pop()->applyTo($bubbled_metadata);
    $this->assertContains('file:' . $file->id(), $bubbled_metadata['#cache']['tags']);
    $this->assertContains('config:image.style.' . $style->getName(), $bubbled_metadata['#cache']['tags']);
  }

  /**
   * Tests image_value_obj skips an image whose file entity is missing.
   */
  public function testImageValueObjSkipsMissingImageFileEntity(): void {
    $this->createImageField('field_image', 'entity_test', 'entity_test');
    $file = File::create(['uri' => 'public://deleted-image.jpg']);
    $file->save();
    $entity = EntityTest::create([
      'name' => 'missing',
      'field_image' => [
        'target_id' => $file->id(),
        'alt' => 'Missing alt',
        'title' => 'Missing title',
      ],
    ]);
    $entity->save();

    $file->delete();
    $entity = EntityTest::load($entity->id());

    $extension = $this->container->get('oe_bootstrap_theme_helper.twig_extension');
    $element = ['child' => ['#item' => $entity->get('field_image')->first()]];
    $this->assertNull($this->imageValueObject($element));
  }

  /**
   * Tests multiple image children yield an array.
   */
  public function testImageValueObjMultipleImagesReturnArray(): void {
    $this->createImageField('field_images', 'entity_test', 'entity_test', [
      'cardinality' => FieldStorageConfig::CARDINALITY_UNLIMITED,
    ]);
    $file_one = $this->createImageFileEntity(0);
    $file_two = $this->createImageFileEntity(1);

    $entity = EntityTest::create([
      'name' => 'multi',
      'field_images' => [
        [
          'target_id' => $file_one->id(),
          'alt' => 'First alt',
          'title' => 'First title',
        ],
        [
          'target_id' => $file_two->id(),
          'alt' => 'Second alt',
          'title' => 'Second title',
        ],
      ],
    ]);
    $entity->save();

    $extension = $this->container->get('oe_bootstrap_theme_helper.twig_extension');
    $items = $entity->get('field_images');
    $element = [
      'child_0' => ['#item' => $items->get(0)],
      'child_1' => ['#item' => $items->get(1)],
    ];
    $result = $this->imageValueObject($element);
    $this->assertIsArray($result);
    $this->assertCount(2, $result);
    $this->assertContainsOnlyInstancesOf(ImageValueObjectInterface::class, $result);
    $this->assertEquals('First title', $result[0]->getName());
    $this->assertEquals('Second title', $result[1]->getName());
  }

  /**
   * Tests invalid children are skipped while valid ones are collected.
   */
  public function testImageValueObjInvalidChildrenAreSkippedNotAborted(): void {
    $this->createImageField('field_image', 'entity_test', 'entity_test');
    FieldStorageConfig::create([
      'entity_type' => 'entity_test',
      'field_name' => 'field_text',
      'type' => 'string',
    ])->save();
    FieldConfig::create([
      'field_storage' => FieldStorageConfig::loadByName('entity_test', 'field_text'),
      'bundle' => 'entity_test',
    ])->save();
    $file = $this->createImageFileEntity();

    $entity = EntityTest::create([
      'name' => 'mixed',
      'field_image' => [
        'target_id' => $file->id(),
        'alt' => 'Single alt',
        'title' => 'Single title',
      ],
      'field_text' => 'not an image',
    ]);
    $entity->save();

    $extension = $this->container->get('oe_bootstrap_theme_helper.twig_extension');
    $element = [
      'invalid' => ['#item' => $entity->get('field_text')->first()],
      'valid' => ['#item' => $entity->get('field_image')->first()],
    ];
    $result = $this->imageValueObject($element);
    $this->assertInstanceOf(ImageValueObjectInterface::class, $result);
    $this->assertEquals('Single title', $result->getName());
  }

  /**
   * Tests idempotency for a single value object.
   */
  public function testImageValueObjIdempotentSingleValueObject(): void {
    $this->createImageField('field_image', 'entity_test', 'entity_test');
    $file = $this->createImageFileEntity();

    $entity = EntityTest::create([
      'name' => 'single',
      'field_image' => [
        'target_id' => $file->id(),
        'alt' => 'Single alt',
        'title' => 'Single title',
      ],
    ]);
    $entity->save();

    $extension = $this->container->get('oe_bootstrap_theme_helper.twig_extension');
    $first = $this->imageValueObject([
      'child' => ['#item' => $entity->get('field_image')->first()],
    ]);
    $this->assertInstanceOf(ImageValueObjectInterface::class, $first);

    $second = $this->imageValueObject($first);
    $this->assertSame($first, $second);
  }

  /**
   * Tests idempotency for an array of value objects.
   */
  public function testImageValueObjIdempotentArrayValueObjects(): void {
    $this->createImageField('field_images', 'entity_test', 'entity_test', [
      'cardinality' => FieldStorageConfig::CARDINALITY_UNLIMITED,
    ]);
    $file_one = $this->createImageFileEntity(0);
    $file_two = $this->createImageFileEntity(1);

    $entity = EntityTest::create([
      'name' => 'multi',
      'field_images' => [
        ['target_id' => $file_one->id(), 'alt' => 'First alt', 'title' => 'First title'],
        ['target_id' => $file_two->id(), 'alt' => 'Second alt', 'title' => 'Second title'],
      ],
    ]);
    $entity->save();

    $extension = $this->container->get('oe_bootstrap_theme_helper.twig_extension');
    $items = $entity->get('field_images');
    $first = $this->imageValueObject([
      'child_0' => ['#item' => $items->get(0)],
      'child_1' => ['#item' => $items->get(1)],
    ]);
    $this->assertIsArray($first);
    $this->assertCount(2, $first);

    $second = $this->imageValueObject($first);
    $this->assertIsArray($second);
    $this->assertCount(2, $second);
    $this->assertSame($first[0], $second[0]);
    $this->assertSame($first[1], $second[1]);
  }

  /**
   * Tests a single-element value object array collapses to the object.
   */
  public function testImageValueObjSingleElementValueObjectArrayCollapses(): void {
    $this->createImageField('field_image', 'entity_test', 'entity_test');
    $file = $this->createImageFileEntity();

    $entity = EntityTest::create([
      'name' => 'single',
      'field_image' => [
        'target_id' => $file->id(),
        'alt' => 'Single alt',
        'title' => 'Single title',
      ],
    ]);
    $entity->save();

    $extension = $this->container->get('oe_bootstrap_theme_helper.twig_extension');
    $single = $this->imageValueObject([
      'child' => ['#item' => $entity->get('field_image')->first()],
    ]);
    $this->assertInstanceOf(ImageValueObjectInterface::class, $single);

    $result = $this->imageValueObject([$single]);
    $this->assertSame($single, $result);
  }

  /**
   * Tests render array properties are skipped.
   */
  public function testImageValueObjRenderPropertiesAreSkipped(): void {
    $this->createImageField('field_image', 'entity_test', 'entity_test');
    $file = $this->createImageFileEntity();

    $entity = EntityTest::create([
      'name' => 'single',
      'field_image' => [
        'target_id' => $file->id(),
        'alt' => 'Single alt',
        'title' => 'Single title',
      ],
    ]);
    $entity->save();

    $extension = $this->container->get('oe_bootstrap_theme_helper.twig_extension');
    $element = [
      '#theme' => 'field',
      '#cache' => ['tags' => ['foo']],
      'child' => ['#item' => $entity->get('field_image')->first()],
    ];
    $result = $this->imageValueObject($element);
    $this->assertInstanceOf(ImageValueObjectInterface::class, $result);
    $this->assertEquals('Single title', $result->getName());
  }

  /**
   * Tests non-array children are skipped.
   */
  public function testImageValueObjScalarChildReturnsNull(): void {
    $extension = $this->container->get('oe_bootstrap_theme_helper.twig_extension');
    $element = ['child' => 'a string, not an array'];
    $this->assertNull($this->imageValueObject($element));
  }

  /**
   * Tests a mixed array of a value object and a render child is combined.
   */
  public function testImageValueObjMixedValueObjectAndRenderChild(): void {
    $this->createImageField('field_images', 'entity_test', 'entity_test', [
      'cardinality' => FieldStorageConfig::CARDINALITY_UNLIMITED,
    ]);
    $file_one = $this->createImageFileEntity(0);
    $file_two = $this->createImageFileEntity(1);

    $entity = EntityTest::create([
      'name' => 'multi',
      'field_images' => [
        ['target_id' => $file_one->id(), 'alt' => 'First alt', 'title' => 'First title'],
        ['target_id' => $file_two->id(), 'alt' => 'Second alt', 'title' => 'Second title'],
      ],
    ]);
    $entity->save();

    $extension = $this->container->get('oe_bootstrap_theme_helper.twig_extension');
    $existing = $this->imageValueObject([
      'child' => ['#item' => $entity->get('field_images')->get(0)],
    ]);
    $this->assertInstanceOf(ImageValueObjectInterface::class, $existing);

    $element = [
      'existing' => $existing,
      'child' => ['#item' => $entity->get('field_images')->get(1)],
    ];
    $result = $this->imageValueObject($element);
    $this->assertIsArray($result);
    $this->assertCount(2, $result);
    $this->assertContainsOnlyInstancesOf(ImageValueObjectInterface::class, $result);
    $this->assertSame($existing, $result[0]);
    $this->assertEquals('Second title', $result[1]->getName());
  }

  /**
   * Creates a permanent image file entity from a core test fixture.
   *
   * @param int $index
   *   Index of the test image file to use.
   *
   * @return \Drupal\file\Entity\File
   *   The saved file entity.
   */
  protected function createImageFileEntity(int $index = 0): File {
    $file = File::create([
      'uri' => $this->getTestFiles('image')[$index]->uri,
    ]);
    $file->save();

    return $file;
  }

  /**
   * Invokes the image_value_obj callback within a render context.
   *
   * @param \Drupal\oe_bootstrap_theme\ValueObject\ImageValueObjectInterface|array|null $element
   *   The value passed to the filter.
   *
   * @return \Drupal\oe_bootstrap_theme\ValueObject\ImageValueObjectInterface|\Drupal\oe_bootstrap_theme\ValueObject\ImageValueObjectInterface[]|null
   *   The filter result.
   */
  protected function imageValueObject(ImageValueObjectInterface|array|null $element): ImageValueObjectInterface|array|null {
    $extension = $this->container->get('oe_bootstrap_theme_helper.twig_extension');
    $renderer = $this->container->get('renderer');
    $render_context = new RenderContext();

    return $renderer->executeInRenderContext($render_context, function () use ($extension, $element) {
      return $extension->imageValueObject($element);
    });
  }

}
