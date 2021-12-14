<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstap_theme\Kernel\Patterns;

use Drupal\Tests\oe_bootstrap_theme\Kernel\Paragraphs\ParagraphsTestBase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Test file pattern rendering.
 */
class FileParagraphTest extends ParagraphsTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->container->get('module_handler')->loadInclude('oe_paragraphs_media_field_storage', 'install');
    oe_paragraphs_media_field_storage_install(FALSE);
    $this->installEntitySchema('node');
    $this->installSchema('node', ['node_access']);
    $this->installEntitySchema('media');
    $this->installConfig([
      'media',
      'oe_media',
      'oe_paragraphs_media',
      'media_avportal',
      'oe_media_avportal',
      'oe_paragraphs_banner',
      'oe_paragraphs_document',
      'oe_paragraphs_iframe_media',
      'options',
      'oe_media_iframe',
      'oe_multilingual',
    ]);

    $this->container->get('module_handler')->loadInclude('oe_multilingual', 'install');
    oe_multilingual_install(FALSE);

    // Call the install hook of the Media module.
    module_load_include('install', 'media');
    media_install();

  }

  /**
   * Test file pattern rendering.
   */
  public function testDocument(): void {
    // Make document media translatable.
    $this->container->get('content_translation.manager')->setEnabled('media', 'document', TRUE);
    // Make fields translatable.
    $field_ids = [
      'media.document.oe_media_file_type',
      'media.document.oe_media_remote_file',
      'media.document.oe_media_file',
    ];
    foreach ($field_ids as $field_id) {
      $field_config = $this->container->get('entity_type.manager')->getStorage('field_config')->load($field_id);
      $field_config->set('translatable', TRUE)->save();
    }
    $this->container->get('router.builder')->rebuild();

    $field_config = $this->container->get('entity_type.manager')->getStorage('field_config')->load('media.document.oe_media_file');
    $field_config->set('translatable', TRUE)->save();
    $this->container->get('router.builder')->rebuild();

    // Create English file.
    $en_image_file = file_save_data(file_get_contents(drupal_get_path('theme', 'oe_bootstrap_theme') . '/tests/fixtures/hello.txt'), 'public://hello.txt');
    $en_image_file->setPermanent();
    $en_image_file->save();

    $media_storage = $this->container->get('entity_type.manager')->getStorage('media');
    /** @var \Drupal\media\MediaInterface $media */
    $media = $media_storage->create([
      'bundle' => 'document',
      'name' => 'druplicon.txt',
      'oe_media_file_type' => 'local',
      'oe_media_file' => [
        'target_id' => (int) $en_image_file->id(),
      ],
    ]);
    $media->save();

    // Create Other language files.
    $other_languages = ['fr', 'it', 'es'];

    foreach ($other_languages as $lang) {
      $image_file[$lang] = file_save_data(file_get_contents(drupal_get_path('theme', 'oe_bootstrap_theme') . '/tests/fixtures/hello.txt'), "public://example_$lang.txt");
      $image_file[$lang]->setPermanent();
      $image_file[$lang]->save();

      $media->addTranslation($lang, [
        'name' => 'test document ' . $lang,
        'oe_media_file_type' => 'local',
        'oe_media_file' => [
          'target_id' => (int) $image_file[$lang]->id(),
        ],
      ]);
      $media->save();
    }

    $paragraph_storage = $this->container->get('entity_type.manager')->getStorage('paragraph');
    $paragraph = $paragraph_storage->create([
      'type' => 'oe_document',
      'field_oe_media' => [
        'target_id' => $media->id(),
      ],
    ]);
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(3, $crawler->filter('div.row'));
    $this->assertCount(4, $crawler->filter('small.fw-bold.m-0'));
    $this->assertCount(1, $crawler->filter('.collapse .py-3.border-bottom:nth-child(1) a'));
    $this->assertCount(1, $crawler->filter('.collapse .py-3.border-bottom:nth-child(2) a'));
    $this->assertCount(1, $crawler->filter('.collapse .pt-3 a'));

    $this->assertEquals('druplicon.txt', $crawler->filter('p.fw-bold.m-0')->text());
    $this->assertEquals('Other languages (3)', $crawler->filter('div.text-end a.text-underline-hover')->text());
    $this->assertEquals('English (12 bytes - TXT)', $crawler->filter('small.fw-bold.m-0')->text());
    $this->assertEquals('Español (12 bytes - TXT)', $crawler->filter('.py-3.border-bottom:nth-child(1) small')->text());
    $this->assertEquals('Français (12 bytes - TXT)', $crawler->filter('.py-3.border-bottom:nth-child(2) small')->text());
    $this->assertEquals('Italiano (12 bytes - TXT)', $crawler->filter('.pt-3 small')->text());

    foreach ($other_languages as $lang) {
      $media->removeTranslation($lang);
      $media->save();
    }

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(0, $crawler->filter('div.row'));
    $this->assertCount(1, $crawler->filter('small.fw-bold.m-0'));

    $this->assertEquals('druplicon.txt', $crawler->filter('p.fw-bold.m-0')->text());
    $this->assertEquals('English (12 bytes - TXT)', $crawler->filter('small.fw-bold.m-0')->text());
    $this->assertStringContainsString('hello.txt', $crawler->filter('.text-underline-hover')->attr('href'));
    $this->assertStringContainsString('/assets/icons/bootstrap-icons.svg#file-text-fill', $crawler->filter('.text-secondary use')->attr('xlink:href'));
  }

}
