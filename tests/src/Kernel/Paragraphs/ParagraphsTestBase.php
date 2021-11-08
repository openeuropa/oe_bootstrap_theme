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
  public static $modules = [
    'language',
    'content_translation',
    'paragraphs',
    'file',
    'field',
    'entity_reference_revisions',
    'datetime',
    'link',
    'text',
    'filter',
    'options',
    'typed_link',
    'oe_paragraphs',
    'allowed_formats',
    'locale',
    'node',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
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
