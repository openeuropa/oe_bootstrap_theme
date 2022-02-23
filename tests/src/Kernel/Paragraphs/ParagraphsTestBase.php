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
    'content_translation',
    'datetime',
    'description_list_field',
    'entity_browser',
    'entity_reference_revisions',
    'field',
    'file',
    'file_link',
    'filter',
    'language',
    'link',
    'locale',
    'media',
    'media_avportal',
    'node',
    'oe_bootstrap_theme_paragraphs',
    'oe_media',
    'oe_media_avportal',
    'oe_media_iframe',
    'oe_multilingual',
    'oe_paragraphs',
    'oe_paragraphs_banner',
    'oe_paragraphs_description_list',
    'oe_paragraphs_document',
    'oe_paragraphs_iframe_media',
    'oe_paragraphs_media',
    'oe_paragraphs_media_field_storage',
    'options',
    'paragraphs',
    'text',
    'typed_link',
    'user',
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
      'oe_paragraphs_description_list',
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
