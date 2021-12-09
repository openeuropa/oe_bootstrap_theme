<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel\Paragraphs;

use Drupal\paragraphs\ParagraphInterface;
use Drupal\Tests\oe_bootstrap_theme\Kernel\AbstractKernelTestBase;

/**
 * Base class for paragraphs tests.
 */
abstract class ParagraphsTestBase extends AbstractKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'allowed_formats',
    'ckeditor',
    'datetime',
    'editor',
    'entity_reference_revisions',
    'entity_browser',
    'file',
    'field',
    'file_link',
    'filter',
    'language',
    'link',
    'locale',
    'media',
    'node',
    'media_avportal',
    'paragraphs',
    'options',
    'oe_media',
    'oe_media_avportal',
    'oe_media_iframe',
    'oe_paragraphs',
    'oe_paragraphs_media',
    'oe_paragraphs_media_field_storage',
    'oe_paragraphs_iframe_media',
    'oe_paragraphs_banner',
    'oe_bootstrap_theme_paragraphs',
    'text',
    'typed_link',
    'views',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('paragraph');
    $this->installEntitySchema('file');
    $this->installSchema('file', ['file_usage']);
    $this->installSchema('locale', [
      'locales_location',
      'locales_source',
      'locales_target',
    ]);
    $this->installConfig([
      'oe_paragraphs',
      'oe_bootstrap_theme_paragraphs',
      'filter',
      'locale',
      'language',
      'node',
    ]);
  }

  /**
   * Render a paragraph.
   *
   * @param \Drupal\paragraphs\ParagraphInterface $paragraph
   *   Paragraph entity.
   *
   * @return string
   *   Rendered output.
   *
   * @throws \Exception
   */
  protected function renderParagraph(ParagraphInterface $paragraph): string {
    $render = \Drupal::entityTypeManager()
      ->getViewBuilder('paragraph')
      ->view($paragraph, 'default');

    return $this->renderRoot($render);
  }

}
