<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel\ValueObject;

use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\oe_bootstrap_theme\ValueObject\ImageValueObject;
use Drupal\Tests\oe_bootstrap_theme\Kernel\AbstractKernelTestBase;

/**
 * Test image value object with image field type. Extracted from oe_theme.
 *
 * @see https://github.com/openeuropa/oe_theme/blob/3.x/tests/src/Kernel/ValueObject/ImageTest.php
 */
class ImageValueObjectTest extends AbstractKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'entity_test',
    'field',
    'file',
    'image',
  ];

  /**
   * Entity object.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('file');
    $this->installEntitySchema('entity_test');
    $this->installSchema('file', ['file_usage']);
    $this->installConfig(['field', 'system']);

    // Create a field with settings to validate.
    $field_storage = FieldStorageConfig::create([
      'entity_type' => 'entity_test',
      'field_name' => 'field_image',
      'type' => 'image',
    ]);
    $field_storage->save();
    FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => 'entity_test',
    ])->save();

    // Copy file in public files to use it for styling.
    \Drupal::service('file_system')->copy(__DIR__ . '/../../../fixtures/example_1.jpeg', 'public://example_1.jpg');

    // Create image file.
    $image = File::create([
      'uri' => 'public://example_1.jpg',
    ]);
    $image->save();

    // Create an image item.
    $this->entity = EntityTest::create([
      'name' => $this->randomString(),
      'field_image' => [
        'target_id' => $image->id(),
        'alt' => 'This is an alternative title',
        'title' => 'This is a title',
      ],
    ]);
    $this->entity->save();
  }

  /**
   * Tests the ::fromStyledImageItem method.
   */
  public function testFromStyledImageItem() {
    // Create a test style.
    /** @var \Drupal\image\ImageStyleInterface $style */
    $style = ImageStyle::create(['name' => 'main_style']);
    $style->save();

    $object = ImageValueObject::fromStyledImageItem($this->entity->get('field_image')->first(), $style->getName());
    $this->assertEquals('This is a title', $object->getName());
    $this->assertEquals('This is an alternative title', $object->getAlt());
    $this->assertStringContainsString('/styles/main_style/public/example_1.jpg', $object->getSource());

    // Test that all the cache tags have present and bubbled up.
    $this->assertEqualsCanonicalizing([
      'config:image.style.main_style',
      'file:1',
    ], $object->getCacheTags());

    $invalid_image_style = $this->randomMachineName();
    $this->expectExceptionObject(new \InvalidArgumentException(sprintf('Could not load image style with name "%s".', $invalid_image_style)));
    ImageValueObject::fromStyledImageItem($this->entity->get('field_image')->first(), $invalid_image_style);
  }

  /**
   * Tests the ::fromImageItem method.
   */
  public function testFromImageItem() {
    $object = ImageValueObject::fromImageItem($this->entity->get('field_image')->first());
    $this->assertEquals('This is a title', $object->getName());
    $this->assertEquals('This is an alternative title', $object->getAlt());
    $this->assertStringContainsString('/files/example_1.jpg', $object->getSource());

    // Test that all the cache tags have present and bubbled up.
    $this->assertEqualsCanonicalizing([
      'file:1',
    ], $object->getCacheTags());
  }

  /**
   * Tests other methods.
   */
  public function testOtherMethods(): void {
    // Test with default values.
    $input_values = [
      // The 'src' is considered required.
      'src' => 'https://example.com/image.png',
    ];
    $object = ImageValueObject::fromArray($input_values);
    $this->assertGetters([
      'src' => 'https://example.com/image.png',
      'alt' => '',
      'name' => '',
      'responsive' => TRUE,
    ], $object);
    $this->assertCacheMetadata([], [], -1, $object);
    $this->assertSame($expected_render = [
      '#theme' => 'image',
      '#uri' => 'https://example.com/image.png',
      '#title' => '',
      '#alt' => '',
      '#cache' => [
        'contexts' => [],
        'tags' => [],
        'max-age' => -1,
      ],
    ], $object->toRenderArray());

    // Test with custom values.
    $input_values = [
      ...$input_values,
      'alt' => 'Example alt text',
      'name' => 'Example name',
      'responsive' => FALSE,
    ];
    $object = ImageValueObject::fromArray($input_values);
    $this->assertGetters($input_values, $object);
    $this->assertCacheMetadata([], [], -1, $object);
    $this->assertSame($expected_render = [
      ...$expected_render,
      '#title' => 'Example name',
      '#alt' => 'Example alt text',
    ], $object->toRenderArray());

    // Test with cache metadata.
    $object->addCacheContexts(['user']);
    $object->addCacheTags(['example-cache-tag']);
    $object->mergeCacheMaxAge(999);
    $this->assertGetters($input_values, $object);
    $this->assertCacheMetadata(['user'], ['example-cache-tag'], 999, $object);
    $this->assertSame([
      ...$expected_render,
      '#cache' => [
        'contexts' => ['user'],
        'tags' => ['example-cache-tag'],
        'max-age' => 999,
      ],
    ], $object->toRenderArray());
  }

  /**
   * Asserts values from getters and other methods.
   *
   * @param array{src: string, alt: string, name: string, responsive: bool} $expected_values
   *   Expected getter values.
   * @param \Drupal\oe_bootstrap_theme\ValueObject\ImageValueObject $object
   *   The object to test.
   */
  protected function assertGetters(array $expected_values, ImageValueObject $object): void {
    $this->assertSame($expected_values, [
      'src' => $object->getSource(),
      'alt' => $object->getAlt(),
      'name' => $object->getName(),
      'responsive' => $object->isResponsive(),
    ]);
    $this->assertEqualsCanonicalizing($expected_values, $object->getArray());
    $this->assertEqualsCanonicalizing($expected_values, iterator_to_array($object->getIterator()));
    foreach ($expected_values as $key => $expected_value) {
      $this->assertSame($expected_value, $object[$key]);
    }
  }

  /**
   * Asserts cache metadata methods.
   *
   * @param list<string> $cache_contexts
   *   The expected cache contexts.
   * @param list<string> $cache_tags
   *   The expected cache tags.
   * @param int $cache_max_age
   *   The expected max-age.
   * @param \Drupal\oe_bootstrap_theme\ValueObject\ImageValueObject $object
   *   The object to test.
   */
  protected function assertCacheMetadata(array $cache_contexts, array $cache_tags, int $cache_max_age, ImageValueObject $object): void {
    $this->assertEqualsCanonicalizing($cache_contexts, $object->getCacheContexts());
    $this->assertEqualsCanonicalizing($cache_tags, $object->getCacheTags());
    $this->assertEqualsCanonicalizing($cache_max_age, $object->getCacheMaxAge());
  }

}
