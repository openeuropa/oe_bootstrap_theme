<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel\Paragraphs;

use Drupal\Core\Url;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the rendering of paragraph types with media fields.
 */
class MediaParagraphsTest extends ParagraphsTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->container->get('module_handler')->loadInclude('oe_paragraphs_media_field_storage', 'install');
    oe_paragraphs_media_field_storage_install(FALSE);
    $this->installEntitySchema('media');
    $this->installConfig([
      'media',
      'oe_media',
      'oe_paragraphs_media',
      'media_avportal',
      'oe_media_avportal',
      'oe_paragraphs_banner',
      'oe_paragraphs_iframe_media',
      'options',
      'oe_media_iframe',
    ]);
    // Call the install hook of the Media module.
    module_load_include('install', 'media');
    media_install();
  }

  /**
   * Test 'text with featured media' paragraph rendering.
   */
  public function testFeaturedMedia(): void {
    $image_file = file_save_data(file_get_contents(drupal_get_path('theme', 'oe_bootstrap_theme') . '/tests/fixtures/example_1.jpeg'), 'public://example_1.jpeg');
    $image_file->setPermanent();
    $image_file->save();

    $media_storage = $this->container->get('entity_type.manager')->getStorage('media');
    $media = $media_storage->create([
      'bundle' => 'image',
      'name' => 'test image',
      'oe_media_image' => [
        'target_id' => $image_file->id(),
        'alt' => 'Alt en',
      ],
    ]);
    $media->save();

    $paragraph_storage = $this->container->get('entity_type.manager')->getStorage('paragraph');
    $paragraph = $paragraph_storage->create([
      'type' => 'oe_text_feature_media',
      'field_oe_title' => 'Media Title',
      'field_oe_plain_text_long' => 'Media Caption',
      'field_oe_media' => [
        'target_id' => $media->id(),
      ],
    ]);
    $paragraph->save();

    // Testing: Image without wrapper.
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('div.row'));
    $this->assertCount(1, $crawler->filter('div.col-12.col-md-4'));
    $this->assertCount(0, $crawler->filter('h3'));
    $this->assertCount(0, $crawler->filter('div.col-12.col-md-6.order-md-1'));
    $this->assertCount(0, $crawler->filter('div.col-12.col-md-6.order-md-2'));
    $figure = $crawler->filter('figure');
    $this->assertCount(1, $figure);
    $this->assertCount(1, $figure->filter('img.img-fluid'));
    $this->assertCount(0, $figure->filter('iframe'));
    $this->assertStringContainsString(
       $image_file->getFilename(),
      $figure->html()
    );
    $this->assertEquals('Media Caption', trim($figure->filter('figcaption.bg-light.p-3')->text()));

    // Testing: Image with wrapper aligned to left.
    $paragraph->get('field_oe_text_long')->setValue('Media Full Text');
    $paragraph->get('oe_paragraphs_variant')->setValue('left_featured');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('div.row'));
    $this->assertCount(0, $crawler->filter('div.col-12.col-md-4'));
    $this->assertCount(1, $crawler->filter('h3'));
    $this->assertCount(1, $crawler->filter('div.col-12.col-md-6.order-md-1'));
    $this->assertCount(1, $crawler->filter('div.col-12.col-md-6.order-md-2'));
    $figure = $crawler->filter('figure');
    $this->assertCount(1, $figure);
    $this->assertCount(1, $figure->filter('img.img-fluid'));
    $this->assertCount(0, $figure->filter('iframe'));
    $this->assertStringContainsString(
      $image_file->getFilename(),
      $figure->html()
    );
    $full_text = $crawler->filter('div.col-12.col-md-6.order-md-1');
    $this->assertEquals('Media Full Text', trim($full_text->text()));
    $this->assertEquals('Media Caption', trim($figure->filter('figcaption.bg-light.p-3')->text()));

    // Testing: Image with wrapper aligned to right.
    $paragraph->get('field_oe_text_long')->setValue('Media Full Text');
    $paragraph->get('oe_paragraphs_variant')->setValue('right_featured');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('div.row'));
    $this->assertCount(0, $crawler->filter('div.col-12.col-md-4'));
    $this->assertCount(1, $crawler->filter('h3'));
    $this->assertCount(1, $crawler->filter('div.col-12.col-md-6.order-md-1'));
    $this->assertCount(1, $crawler->filter('div.col-12.col-md-6.order-md-2'));
    $figure = $crawler->filter('figure');
    $this->assertCount(1, $figure);
    $this->assertCount(1, $figure->filter('img.img-fluid'));
    $this->assertCount(0, $figure->filter('iframe'));
    $this->assertStringContainsString(
      $image_file->getFilename(),
      $figure->html()
    );
    $full_text = $crawler->filter('div.col-12.col-md-6.order-md-2');
    $this->assertEquals('Media Full Text', trim($full_text->text()));
    $this->assertEquals('Media Caption', trim($figure->filter('figcaption.bg-light.p-3')->text()));

    // Create a remote video and add it to the paragraph.
    $media = $media_storage->create([
      'bundle' => 'remote_video',
      'oe_media_oembed_video' => [
        'value' => 'https://www.youtube.com/watch?v=1-g73ty9v04',
      ],
    ]);
    $media->save();
    $paragraph->set('field_oe_media', ['target_id' => $media->id()]);
    $paragraph->save();

    $paragraph_storage = $this->container->get('entity_type.manager')->getStorage('paragraph');
    $paragraph = $paragraph_storage->create([
      'type' => 'oe_text_feature_media',
      'field_oe_title' => 'Media Title',
      'field_oe_plain_text_long' => 'Media Caption',
      'field_oe_media' => [
        'target_id' => $media->id(),
      ],
    ]);
    $paragraph->save();

    // Testing: Iframe without wrapper.
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('div.row'));
    $this->assertCount(1, $crawler->filter('div.col-12.col-md-4'));
    $this->assertCount(0, $crawler->filter('h3'));
    $this->assertCount(0, $crawler->filter('div.col-12.col-md-6.order-md-1'));
    $this->assertCount(0, $crawler->filter('div.col-12.col-md-6.order-md-2'));
    $this->assertCount(1, $crawler->filter('div.ratio.ratio-16x9'));
    $figure = $crawler->filter('figure');
    $this->assertCount(1, $figure);
    $this->assertCount(0, $figure->filter('img.img-fluid'));
    $this->assertCount(1, $figure->filter('iframe'));
    // Assert remote video is rendered properly.
    $video_iframe = $crawler->filter('iframe');
    $partial_iframe_url = Url::fromRoute('media.oembed_iframe', [], [
      'query' => [
        'url' => 'https://www.youtube.com/watch?v=1-g73ty9v04',
      ],
    ])->toString();
    $this->assertStringContainsString($partial_iframe_url, $video_iframe->attr('src'));
    $this->assertEquals('Media Caption', trim($figure->filter('figcaption.bg-light.p-3')->text()));

    // Testing: Iframe with wrapper aligned to left.
    $paragraph->get('field_oe_text_long')->setValue('Media Full Text');
    $paragraph->get('oe_paragraphs_variant')->setValue('left_featured');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('div.row'));
    $this->assertCount(0, $crawler->filter('div.col-12.col-md-4'));
    $this->assertCount(1, $crawler->filter('h3'));
    $this->assertCount(1, $crawler->filter('div.col-12.col-md-6.order-md-1'));
    $this->assertCount(1, $crawler->filter('div.col-12.col-md-6.order-md-2'));
    $this->assertCount(1, $crawler->filter('div.ratio.ratio-16x9'));
    $figure = $crawler->filter('figure');
    $this->assertCount(1, $figure);
    $this->assertCount(0, $figure->filter('img.img-fluid'));
    $this->assertCount(1, $figure->filter('iframe'));
    // Assert remote video is rendered properly.
    $video_iframe = $crawler->filter('iframe');
    $partial_iframe_url = Url::fromRoute('media.oembed_iframe', [], [
      'query' => [
        'url' => 'https://www.youtube.com/watch?v=1-g73ty9v04',
      ],
    ])->toString();
    $this->assertStringContainsString($partial_iframe_url, $video_iframe->attr('src'));
    $full_text = $crawler->filter('div.col-12.col-md-6.order-md-1');
    $this->assertEquals('Media Full Text', trim($full_text->text()));
    $this->assertEquals('Media Caption', trim($figure->filter('figcaption.bg-light.p-3')->text()));

    // Testing: Iframe with wrapper aligned to right.
    $paragraph->get('field_oe_text_long')->setValue('Media Full Text');
    $paragraph->get('oe_paragraphs_variant')->setValue('right_featured');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('div.row'));
    $this->assertCount(0, $crawler->filter('div.col-12.col-md-4'));
    $this->assertCount(1, $crawler->filter('h3'));
    $this->assertCount(1, $crawler->filter('div.col-12.col-md-6.order-md-1'));
    $this->assertCount(1, $crawler->filter('div.col-12.col-md-6.order-md-2'));
    $this->assertCount(1, $crawler->filter('div.ratio.ratio-16x9'));
    $figure = $crawler->filter('figure');
    $this->assertCount(1, $figure);
    $this->assertCount(0, $figure->filter('img.img-fluid'));
    $this->assertCount(1, $figure->filter('iframe'));
    // Assert remote video is rendered properly.
    $video_iframe = $crawler->filter('iframe');
    $partial_iframe_url = Url::fromRoute('media.oembed_iframe', [], [
      'query' => [
        'url' => 'https://www.youtube.com/watch?v=1-g73ty9v04',
      ],
    ])->toString();
    $this->assertStringContainsString($partial_iframe_url, $video_iframe->attr('src'));
    $full_text = $crawler->filter('div.col-12.col-md-6.order-md-2');
    $this->assertEquals('Media Full Text', trim($full_text->text()));
    $this->assertEquals('Media Caption', trim($figure->filter('figcaption.bg-light.p-3')->text()));

    // Create an avportal video and add it to the paragraph.
    $media = $media_storage->create([
      'bundle' => 'av_portal_video',
      'oe_media_avportal_video' => 'I-163162',
    ]);
    $media->save();
    $paragraph->set('field_oe_media', ['target_id' => $media->id()]);
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('div.row'));
    $this->assertCount(0, $crawler->filter('div.col-12.col-md-4'));
    $this->assertCount(1, $crawler->filter('h3'));
    $this->assertCount(1, $crawler->filter('div.col-12.col-md-6.order-md-1'));
    $this->assertCount(1, $crawler->filter('div.col-12.col-md-6.order-md-2'));
    $this->assertCount(1, $crawler->filter('div.ratio.ratio-16x9'));
    $figure = $crawler->filter('figure');
    $this->assertCount(1, $figure);
    $this->assertCount(0, $figure->filter('img.img-fluid'));
    $this->assertCount(1, $figure->filter('iframe'));
    // Assert remote video is rendered properly.
    $video_iframe = $crawler->filter('iframe');
    $this->assertStringContainsString('ec.europa.eu/avservices/play.cfm?ref=I-163162', $video_iframe->attr('src'));
    $full_text = $crawler->filter('div.col-12.col-md-6.order-md-2');
    $this->assertEquals('Media Full Text', trim($full_text->text()));
    $this->assertEquals('Media Caption', trim($figure->filter('figcaption.bg-light.p-3')->text()));
  }

  /**
   * Test 'banner' paragraph rendering.
   */
  public function testBanner(): void {
    // Create English file.
    $en_file = file_save_data(file_get_contents(drupal_get_path('theme', 'oe_bootstrap_theme') . '/tests/fixtures/example_1.jpeg'), 'public://example_1_en.jpeg');
    $en_file->setPermanent();
    $en_file->save();

    // Create a media.
    $media_storage = $this->container->get('entity_type.manager')->getStorage('media');
    $media = $media_storage->create([
      'bundle' => 'image',
      'name' => 'test image en',
      'oe_media_image' => [
        'target_id' => $en_file->id(),
        'alt' => 'Alt en',
      ],
    ]);
    $media->save();

    $paragraph_storage = $this->container->get('entity_type.manager')->getStorage('paragraph');
    $paragraph = $paragraph_storage->create([
      'type' => 'oe_banner',
      'oe_paragraphs_variant' => 'oe_banner_image',
      'field_oe_title' => 'Banner',
      'field_oe_text' => 'Description',
      'field_oe_link' => [
        'uri' => 'http://www.example.com/',
        'title' => 'Example',
      ],
      'field_oe_media' => [
        'target_id' => $media->id(),
      ],
      'field_oe_banner_type' => 'hero_center',
    ]);
    $paragraph->save();

    // Variant - image / Modifier - hero_center / Full width - No.
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('.bcl-banner.bg-light.text-dark.overlay.text-center.hero'));
    $image_element = $crawler->filter('.bcl-banner__image');
    $this->assertCount(1, $image_element);
    $this->assertStringContainsString(
      'url(' . file_create_url($en_file->getFileUri()) . ')',
      $image_element->attr('style')
    );
    $this->assertEquals('Banner', trim($crawler->filter('.bcl-banner__content div')->text()));
    $this->assertEquals('Description', trim($crawler->filter('.bcl-banner__content p')->text()));
    $this->assertCount(1, $crawler->filter('svg.bi.icon--fluid'));
    $this->assertStringContainsString('Example', trim($crawler->filter('a.btn.btn-primary')->text()));
    $this->assertStringContainsString('#chevron-right', trim($crawler->filter('a.btn.btn-primary')->html()));
    $this->assertCount(0, $crawler->filter('.bcl-banner.full-width'));

    // Variant - image / Modifier - hero_left / Full width - No.
    $paragraph->get('field_oe_banner_type')->setValue('hero_left');
    $paragraph->save();

    // Unpublish the media and assert it is not rendered anymore.
    $media->set('status', 0);
    $media->save();

    // Publish the media.
    $media->set('status', 1);
    $media->save();

    // Since static cache is not cleared due to lack of requests in the test we
    // need to reset manually.
    $this->container->get('entity_type.manager')->getAccessControlHandler('media')->resetCache();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(0, $crawler->filter('.bcl-banner.bg-light.text-dark.overlay.text-center.hero'));
    $this->assertCount(1, $crawler->filter('.bcl-banner.bg-light.text-dark.overlay.hero'));
    $image_element = $crawler->filter('.bcl-banner__image');
    $this->assertCount(1, $image_element);
    $this->assertStringContainsString(
      'url(' . file_create_url($en_file->getFileUri()) . ')',
      $image_element->attr('style')
    );
    $this->assertEquals('Banner', trim($crawler->filter('.bcl-banner__content div')->text()));
    $this->assertEquals('Description', trim($crawler->filter('.bcl-banner__content p')->text()));
    $this->assertCount(1, $crawler->filter('svg.bi.icon--fluid'));
    $this->assertStringContainsString('Example', trim($crawler->filter('a.btn.btn-primary')->text()));
    $this->assertStringContainsString('#chevron-right', trim($crawler->filter('a.btn.btn-primary')->html()));
    $this->assertCount(0, $crawler->filter('.bcl-banner.full-width'));

    // Variant - image / Modifier - page_center / Full width - No.
    $paragraph->get('field_oe_banner_type')->setValue('page_center');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('.bcl-banner.bg-light.text-dark.overlay.text-center'));
    $this->assertCount(0, $crawler->filter('.bcl-banner.hero'));
    $image_element = $crawler->filter('.bcl-banner__image');
    $this->assertCount(1, $image_element);
    $this->assertStringContainsString(
      'url(' . file_create_url($en_file->getFileUri()) . ')',
      $image_element->attr('style')
    );
    $this->assertEquals('Banner', trim($crawler->filter('.bcl-banner__content div')->text()));
    $this->assertEquals('Description', trim($crawler->filter('.bcl-banner__content p')->text()));
    $this->assertCount(1, $crawler->filter('svg.bi.icon--fluid'));
    $this->assertStringContainsString('Example', trim($crawler->filter('a.btn.btn-primary')->text()));
    $this->assertStringContainsString('#chevron-right', trim($crawler->filter('a.btn.btn-primary')->html()));
    $this->assertCount(0, $crawler->filter('.bcl-banner.full-width'));

    // Variant - image / Modifier - page_left / Full width - Yes.
    $paragraph->get('field_oe_banner_type')->setValue('page_left');
    $paragraph->get('field_oe_banner_full_width')->setValue('1');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('.bcl-banner.bg-light.text-dark.overlay.full-width'));
    $this->assertCount(0, $crawler->filter('.bcl-banner.text-center'));
    $this->assertCount(0, $crawler->filter('.bcl-banner.hero'));
    $image_element = $crawler->filter('.bcl-banner__image');
    $this->assertCount(1, $image_element);
    $this->assertStringContainsString(
      'url(' . file_create_url($en_file->getFileUri()) . ')',
      $image_element->attr('style')
    );
    $this->assertEquals('Banner', trim($crawler->filter('.bcl-banner__content div')->text()));
    $this->assertEquals('Description', trim($crawler->filter('.bcl-banner__content p')->text()));
    $this->assertCount(1, $crawler->filter('svg.bi.icon--fluid'));
    $this->assertStringContainsString('Example', trim($crawler->filter('a.btn.btn-primary')->text()));
    $this->assertStringContainsString('#chevron-right', trim($crawler->filter('a.btn.btn-primary')->html()));
    $this->assertCount(1, $crawler->filter('.bcl-banner.full-width'));

    // Variant - image-shade / Modifier - hero_center / Full width - Yes.
    $paragraph->get('oe_paragraphs_variant')->setValue('oe_banner_image_shade');
    $paragraph->get('field_oe_banner_type')->setValue('hero_center');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('.bcl-banner.bg-light.shade.text-center.hero.full-width.text-white'));
    $image_element = $crawler->filter('.bcl-banner__image');
    $this->assertCount(1, $image_element);
    $this->assertStringContainsString(
      'url(' . file_create_url($en_file->getFileUri()) . ')',
      $image_element->attr('style')
    );
    $this->assertEquals('Banner', trim($crawler->filter('.bcl-banner__content div')->text()));
    $this->assertEquals('Description', trim($crawler->filter('.bcl-banner__content p')->text()));
    $this->assertCount(1, $crawler->filter('svg.bi.icon--fluid'));
    $this->assertStringContainsString('Example', trim($crawler->filter('a.btn.btn-primary')->text()));
    $this->assertStringContainsString('#chevron-right', trim($crawler->filter('a.btn.btn-primary')->html()));
    $this->assertCount(1, $crawler->filter('.bcl-banner.full-width'));

    // Variant - image-shade / Modifier - hero_left / Full width - Yes.
    $paragraph->get('field_oe_banner_type')->setValue('hero_left');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('.bcl-banner.bg-light.shade.hero.full-width.text-white'));
    $this->assertCount(0, $crawler->filter('.bcl-banner.text-center'));
    $image_element = $crawler->filter('.bcl-banner__image');
    $this->assertCount(1, $image_element);
    $this->assertStringContainsString(
      'url(' . file_create_url($en_file->getFileUri()) . ')',
      $image_element->attr('style')
    );
    $this->assertEquals('Banner', trim($crawler->filter('.bcl-banner__content div')->text()));
    $this->assertEquals('Description', trim($crawler->filter('.bcl-banner__content p')->text()));
    $this->assertCount(1, $crawler->filter('svg.bi.icon--fluid'));
    $this->assertStringContainsString('Example', trim($crawler->filter('a.btn.btn-primary')->text()));
    $this->assertStringContainsString('#chevron-right', trim($crawler->filter('a.btn.btn-primary')->html()));
    $this->assertCount(1, $crawler->filter('.bcl-banner.full-width'));

    // Variant - image-shade / Modifier - page_center / Full width - No.
    $paragraph->get('field_oe_banner_type')->setValue('page_center');
    $paragraph->get('field_oe_banner_full_width')->setValue('0');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('.bcl-banner.bg-light.shade.text-center.text-white'));
    $this->assertCount(0, $crawler->filter('.bcl-banner.hero'));
    $image_element = $crawler->filter('.bcl-banner__image');
    $this->assertCount(1, $image_element);
    $this->assertStringContainsString(
      'url(' . file_create_url($en_file->getFileUri()) . ')',
      $image_element->attr('style')
    );
    $this->assertEquals('Banner', trim($crawler->filter('.bcl-banner__content div')->text()));
    $this->assertEquals('Description', trim($crawler->filter('.bcl-banner__content p')->text()));
    $this->assertCount(1, $crawler->filter('svg.bi.icon--fluid'));
    $this->assertStringContainsString('Example', trim($crawler->filter('a.btn.btn-primary')->text()));
    $this->assertStringContainsString('#chevron-right', trim($crawler->filter('a.btn.btn-primary')->html()));
    $this->assertCount(0, $crawler->filter('.bcl-banner.full-width'));

    // Variant - image-shade / Modifier - page_left / Full width - No.
    $paragraph->get('field_oe_banner_type')->setValue('page_left');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('.bcl-banner.bg-light.shade.text-white'));
    $this->assertCount(0, $crawler->filter('.bcl-banner.hero'));
    $this->assertCount(0, $crawler->filter('.bcl-banner.text-center'));
    $image_element = $crawler->filter('.bcl-banner__image');
    $this->assertCount(1, $image_element);
    $this->assertStringContainsString(
      'url(' . file_create_url($en_file->getFileUri()) . ')',
      $image_element->attr('style')
    );
    $this->assertEquals('Banner', trim($crawler->filter('.bcl-banner__content div')->text()));
    $this->assertEquals('Description', trim($crawler->filter('.bcl-banner__content p')->text()));
    $this->assertCount(1, $crawler->filter('svg.bi.icon--fluid'));
    $this->assertStringContainsString('Example', trim($crawler->filter('a.btn.btn-primary')->text()));
    $this->assertStringContainsString('#chevron-right', trim($crawler->filter('a.btn.btn-primary')->html()));
    $this->assertCount(0, $crawler->filter('.bcl-banner.full-width'));

    // Variant - default / Modifier - hero_center / Full width - No.
    $paragraph->get('oe_paragraphs_variant')->setValue('default');
    $paragraph->get('field_oe_banner_type')->setValue('hero_center');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('.bcl-banner.bg-light.text-dark.text-center.hero'));

    // No image should be displayed on 'default' variant.
    $image_element = $crawler->filter('.bcl-banner__image');
    $this->assertCount(0, $image_element);

    $this->assertEquals('Banner', trim($crawler->filter('.bcl-banner__content div')->text()));
    $this->assertEquals('Description', trim($crawler->filter('.bcl-banner__content p')->text()));
    $this->assertCount(1, $crawler->filter('svg.bi.icon--fluid'));
    $this->assertStringContainsString('Example', trim($crawler->filter('a.btn.btn-primary')->text()));
    $this->assertStringContainsString('#chevron-right', trim($crawler->filter('a.btn.btn-primary')->html()));
    $this->assertCount(0, $crawler->filter('.bcl-banner.full-width'));

    // Variant - default / Modifier - hero_left / Full width - Yes.
    $paragraph->get('field_oe_banner_type')->setValue('hero_left');
    $paragraph->get('field_oe_banner_full_width')->setValue('1');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('.bcl-banner.bg-light.text-dark.hero.full-width'));
    $this->assertCount(0, $crawler->filter('.bcl-banner.text-center'));

    // No image should be displayed on 'default' variant.
    $image_element = $crawler->filter('.bcl-banner__image');
    $this->assertCount(0, $image_element);

    $this->assertEquals('Banner', trim($crawler->filter('.bcl-banner__content div')->text()));
    $this->assertEquals('Description', trim($crawler->filter('.bcl-banner__content p')->text()));
    $this->assertCount(1, $crawler->filter('svg.bi.icon--fluid'));
    $this->assertStringContainsString('Example', trim($crawler->filter('a.btn.btn-primary')->text()));
    $this->assertStringContainsString('#chevron-right', trim($crawler->filter('a.btn.btn-primary')->html()));
    $this->assertCount(1, $crawler->filter('.bcl-banner.full-width'));

    // Variant - default / Modifier - page_center / Full width - Yes.
    $paragraph->get('field_oe_banner_type')->setValue('page_center');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('.bcl-banner.bg-light.text-dark.text-center.full-width'));
    $this->assertCount(0, $crawler->filter('.bcl-banner.hero'));

    // No image should be displayed on 'default' variant.
    $image_element = $crawler->filter('.bcl-banner__image');
    $this->assertCount(0, $image_element);

    $this->assertEquals('Banner', trim($crawler->filter('.bcl-banner__content div')->text()));
    $this->assertEquals('Description', trim($crawler->filter('.bcl-banner__content p')->text()));
    $this->assertCount(1, $crawler->filter('svg.bi.icon--fluid'));
    $this->assertStringContainsString('Example', trim($crawler->filter('a.btn.btn-primary')->text()));
    $this->assertStringContainsString('#chevron-right', trim($crawler->filter('a.btn.btn-primary')->html()));
    $this->assertCount(1, $crawler->filter('.bcl-banner.full-width'));

    // Variant - default / Modifier - page_left / Full width - Yes.
    $paragraph->get('field_oe_banner_type')->setValue('page_left');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('.bcl-banner.bg-light.text-dark.full-width'));
    $this->assertCount(0, $crawler->filter('.bcl-banner.hero'));
    $this->assertCount(0, $crawler->filter('.bcl-banner.text-center'));

    // No image should be displayed on 'default' variant.
    $image_element = $crawler->filter('.bcl-banner__image');
    $this->assertCount(0, $image_element);

    $this->assertEquals('Banner', trim($crawler->filter('.bcl-banner__content div')->text()));
    $this->assertEquals('Description', trim($crawler->filter('.bcl-banner__content p')->text()));
    $this->assertCount(1, $crawler->filter('svg.bi.icon--fluid'));
    $this->assertStringContainsString('Example', trim($crawler->filter('a.btn.btn-primary')->text()));
    $this->assertStringContainsString('#chevron-right', trim($crawler->filter('a.btn.btn-primary')->html()));
    $this->assertCount(1, $crawler->filter('.bcl-banner.full-width'));

    // Variant - primary / Modifier - hero_center / Full width - Yes.
    $paragraph->get('oe_paragraphs_variant')->setValue('oe_banner_primary');
    $paragraph->get('field_oe_banner_type')->setValue('hero_center');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('.bcl-banner.bg-primary.text-white.text-center.hero.full-width'));

    // No image should be displayed on 'default' variant.
    $image_element = $crawler->filter('.bcl-banner__image');
    $this->assertCount(0, $image_element);

    $this->assertEquals('Banner', trim($crawler->filter('.bcl-banner__content div')->text()));
    $this->assertEquals('Description', trim($crawler->filter('.bcl-banner__content p')->text()));
    $this->assertCount(1, $crawler->filter('svg.bi.icon--fluid'));
    $this->assertStringContainsString('Example', trim($crawler->filter('a.btn.btn-light')->text()));
    $this->assertStringContainsString('#chevron-right', trim($crawler->filter('a.btn.btn-light')->html()));
    $this->assertCount(1, $crawler->filter('.bcl-banner.full-width'));

    // Variant - primary / Modifier - hero_left / Full width - Yes.
    $paragraph->get('field_oe_banner_type')->setValue('hero_left');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('.bcl-banner.bg-primary.text-white.hero.full-width'));
    $this->assertCount(0, $crawler->filter('.bcl-banner.text-center'));

    // No image should be displayed on 'default' variant.
    $image_element = $crawler->filter('.bcl-banner__image');
    $this->assertCount(0, $image_element);

    $this->assertEquals('Banner', trim($crawler->filter('.bcl-banner__content div')->text()));
    $this->assertEquals('Description', trim($crawler->filter('.bcl-banner__content p')->text()));
    $this->assertCount(1, $crawler->filter('svg.bi.icon--fluid'));
    $this->assertStringContainsString('Example', trim($crawler->filter('a.btn.btn-light')->text()));
    $this->assertStringContainsString('#chevron-right', trim($crawler->filter('a.btn.btn-light')->html()));
    $this->assertCount(1, $crawler->filter('.bcl-banner.full-width'));

    // Variant - primary / Modifier - page_center / Full width - Yes.
    $paragraph->get('field_oe_banner_type')->setValue('page_center');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('.bcl-banner.bg-primary.text-white.text-center.full-width'));
    $this->assertCount(0, $crawler->filter('.bcl-banner.hero'));

    // No image should be displayed on 'default' variant.
    $image_element = $crawler->filter('.bcl-banner__image');
    $this->assertCount(0, $image_element);

    $this->assertEquals('Banner', trim($crawler->filter('.bcl-banner__content div')->text()));
    $this->assertEquals('Description', trim($crawler->filter('.bcl-banner__content p')->text()));
    $this->assertCount(1, $crawler->filter('svg.bi.icon--fluid'));
    $this->assertStringContainsString('Example', trim($crawler->filter('a.btn.btn-light')->text()));
    $this->assertStringContainsString('#chevron-right', trim($crawler->filter('a.btn.btn-light')->html()));
    $this->assertCount(1, $crawler->filter('.bcl-banner.full-width'));

    // Variant - primary / Modifier - page_left / Full width - No.
    $paragraph->get('field_oe_banner_type')->setValue('page_left');
    $paragraph->get('field_oe_banner_full_width')->setValue('0');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('.bcl-banner.bg-primary.text-white'));
    $this->assertCount(0, $crawler->filter('.bcl-banner.hero'));
    $this->assertCount(0, $crawler->filter('.bcl-banner.text-center'));

    // No image should be displayed on 'default' variant.
    $image_element = $crawler->filter('.bcl-banner__image');
    $this->assertCount(0, $image_element);

    $this->assertEquals('Banner', trim($crawler->filter('.bcl-banner__content div')->text()));
    $this->assertEquals('Description', trim($crawler->filter('.bcl-banner__content p')->text()));
    $this->assertCount(1, $crawler->filter('svg.bi.icon--fluid'));
    $this->assertStringContainsString('Example', trim($crawler->filter('a.btn.btn-light')->text()));
    $this->assertStringContainsString('#chevron-right', trim($crawler->filter('a.btn.btn-light')->html()));
    $this->assertCount(0, $crawler->filter('.bcl-banner.full-width'));

    // Create a media using AV Portal image and add it to the paragraph.
    $media = $media_storage->create([
      'bundle' => 'av_portal_photo',
      'oe_media_avportal_photo' => 'P-038924/00-15',
      'uid' => 0,
      'status' => 1,
    ]);

    $media->save();

    $paragraph = $paragraph_storage->create([
      'type' => 'oe_banner',
      'oe_paragraphs_variant' => 'oe_banner_image',
      'field_oe_text' => 'Description',
      'field_oe_media' => [
        'target_id' => $media->id(),
      ],
      'field_oe_banner_type' => 'hero_center',
    ]);
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $image_element = $crawler->filter('.bcl-banner__image');
    $this->assertCount(1, $image_element);
    $this->assertStringContainsString(
      'url(' . (file_create_url('avportal://P-038924/00-15.jpg')) . ')',
      $image_element->attr('style')
    );
  }

}
