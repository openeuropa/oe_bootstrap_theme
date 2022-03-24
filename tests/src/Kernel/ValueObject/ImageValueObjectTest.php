<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel\ValueObject;

use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\oe_bootstrap_theme\ValueObject\ImageValueObject;
use Drupal\Tests\token\Kernel\KernelTestBase;

/**
 * Test image value object with image field type. Extracted from oe_theme.
 *
 * @see https://github.com/openeuropa/oe_theme/blob/3.x/tests/src/Kernel/ValueObject/ImageTest.php
 */
class ImageValueObjectTest extends KernelTestBase {
  private const ALT_TITLE = 'This is an alternative title';
  private const TITLE = 'This is a Title';

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'image',
    'file',
    'entity_test',
    'field',
  ];

  /**
   * Entity object.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  private $entity;

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
    $alt = self::ALT_TITLE;
    $title = self::TITLE;
    $this->entity = EntityTest::create([
      'name' => $this->randomString(),
      'field_image' => [
        'target_id' => $image->id(),
        'alt' => $alt,
        'title' => $title,
      ],
    ]);
    $this->entity->save();
  }

  /**
   * Test the image value object has the correct style applied.
   */
  public function testFromStyledImageItem() {
    // Create a test style.
    /** @var \Drupal\image\ImageStyleInterface $style */
    $style = ImageStyle::create(['name' => 'main_style']);
    $style->save();

    $object = ImageValueObject::fromStyledImageItem($this->entity->get('field_image')->first(), $style->getName());
    $this->assertEquals(self::TITLE, $object->getName());
    $this->assertEquals(self::ALT_TITLE, $object->getAlt());
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
   * Test the image value object from imageItem.
   */
  public function testFromImageItem() {
    $object = ImageValueObject::fromImageItem($this->entity->get('field_image')->first());
    $this->assertEquals(self::TITLE, $object->getName());
    $this->assertEquals(self::ALT_TITLE, $object->getAlt());
    $this->assertStringContainsString('/files/example_1.jpg', $object->getSource());

    // Test that all the cache tags have present and bubbled up.
    $this->assertEqualsCanonicalizing([
      'file:1',
    ], $object->getCacheTags());
  }

}
