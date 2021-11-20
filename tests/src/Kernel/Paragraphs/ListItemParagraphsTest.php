<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel\Paragraphs;

use Symfony\Component\DomCrawler\Crawler;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;

/**
 * Tests the rendering of paragraph List Items.
 */
class ListItemParagraphsTest extends ParagraphsTestBase {

  use NodeCreationTrait;
  use ContentTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('node');
    $this->installSchema('node', ['node_access']);
  }

  /**
   * Test 'List Item' paragraph rendering.
   */
  public function testListItem(): void {
    // Create English file.
    $en_file = file_save_data(file_get_contents(drupal_get_path('theme', 'oe_bootstrap_theme') . '/tests/fixtures/arch.jpeg'), 'public://arch_en.jpeg');
    $en_file->setPermanent();
    $en_file->save();

    $paragraph_storage = $this->container->get('entity_type.manager')->getStorage('paragraph');
    $paragraph = $paragraph_storage->create([
      'type' => 'oe_list_item',
      'oe_paragraphs_variant' => 'default',
      'field_oe_title' => 'Card Title 1',
      'field_oe_text_long' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus ut ex tristique, dignissim sem ac, bibendum est. Sed vehicula lorem non nunc tincidunt hendrerit. Nunc tristique ante et fringilla fermentum.',
      'field_oe_link' => [
        'uri' => 'http://www.example.com/',
        'title' => 'Example',
      ],
      'field_oe_image' => [
        'title' => 'Example Image',
        'alt' => 'Alt for image',
        'target_id' => $en_file->id(),
      ],
      'field_oe_meta' => [
        0 => [
          'value' => 'Dot1',
        ],
        1 => [
          'value' => 'Dot2',
        ],
      ],
    ]);
    $paragraph->save();

    // Variant - default.
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('div.card'));
    $image_element = $crawler->filter('.card-img-top');
    $this->assertCount(1, $image_element);
    $this->assertStringContainsString(
      file_url_transform_relative(file_create_url($en_file->getFileUri())),
      $image_element->attr('src')
    );
    $this->assertCount(1, $crawler->filter('div.card-body'));
    $this->assertCount(1, $crawler->filter('h5.card-title'));
    $this->assertStringContainsString('Dot1', trim($crawler->filter('span[class="me-2 badge bg-primary"]')->text()));
    $this->assertStringContainsString('Dot2', trim($crawler->filter('span[class="badge bg-primary"]')->text()));
    $this->assertStringContainsString('Card Title 1', trim($crawler->filter('h5.card-title')->text()));
    $link_element = $crawler->filter('h5.card-title a');
    $this->assertCount(1, $link_element);
    $this->assertStringContainsString(
      'http://www.example.com/',
      $link_element->attr('href')
    );
    $text_element = $crawler->filter('p.card-text.mb-3');
    $this->assertCount(1, $text_element);
    $this->assertStringContainsString(
      'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus ut ex tristique, dignissim sem ac, bibendum est. Sed vehicula lorem non nunc tincidunt hendrerit. Nunc tristique ante et fringilla fermentum.',
      $text_element->text()
    );
    $this->assertCount(1, $crawler->filter('a.text-underline-hover'));

    // Variant - date / Image - No / Date - Yes.
    $paragraph->get('oe_paragraphs_variant')->setValue('date');
    $paragraph->get('field_oe_image')->setValue([]);
    $paragraph->get('field_oe_date')->setValue('2011-11-13');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('div.card'));
    $this->assertCount(1, $crawler->filter('div.card-body'));
    $this->assertCount(1, $crawler->filter('h5.card-title'));
    $this->assertStringContainsString('Dot1', trim($crawler->filter('span[class="me-2 badge bg-primary"]')->text()));
    $this->assertStringContainsString('Dot2', trim($crawler->filter('span[class="badge bg-primary"]')->text()));
    $this->assertStringContainsString('Card Title 1', trim($crawler->filter('h5.card-title')->text()));
    $link_element = $crawler->filter('h5.card-title a');
    $this->assertCount(1, $link_element);
    $this->assertStringContainsString(
      'http://www.example.com/',
      $link_element->attr('href')
    );
    $text_element = $crawler->filter('p.card-text.mb-3');
    $this->assertCount(1, $text_element);
    $this->assertStringContainsString(
      'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus ut ex tristique, dignissim sem ac, bibendum est. Sed vehicula lorem non nunc tincidunt hendrerit. Nunc tristique ante et fringilla fermentum.',
      $text_element->text()
    );
    $this->assertCount(1, $crawler->filter('a.text-underline-hover'));

    $this->assertCount(1, $crawler->filter('time.bcl-date-block.d-flex.flex-column.align-items-center.justify-content-center.bg-date.text-light.pt-3.rounded-top.mw-date'));
    $this->assertCount(1, $crawler->filter('time[datetime="2011-11-13T12:00:00Z"]'));
    $month_element = $crawler->filter('span[class="pb-3 text-uppercase"]');
    $this->assertCount(1, $month_element);
    $this->assertStringContainsString(
      'Nov',
      $month_element->text()
    );
    $year_element = $crawler->filter('span[class="bg-light w-100 text-center py-2 text-dark rounded-bottom mb-n1"]');
    $this->assertCount(1, $year_element);
    $this->assertStringContainsString(
      '2011',
      $year_element->text()
    );

    // Variant - highlight / Date - No.
    $paragraph->get('oe_paragraphs_variant')->setValue('highlight');
    $paragraph->get('field_oe_image')->setValue([
      'title' => 'Example Image',
      'alt' => 'Alt for image',
      'target_id' => $en_file->id(),
    ]);
    $paragraph->get('field_oe_date')->setValue('');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);

    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('div.card'));
    $image_element = $crawler->filter('.card-img-top');
    $this->assertCount(1, $image_element);
    $this->assertStringContainsString(
      file_url_transform_relative(file_create_url($en_file->getFileUri())),
      $image_element->attr('src')
    );
    $this->assertCount(1, $crawler->filter('div.card-body'));
    $this->assertCount(1, $crawler->filter('h5.card-title'));
    $this->assertStringContainsString('Dot1', trim($crawler->filter('span[class="me-2 badge bg-primary"]')->text()));
    $this->assertStringContainsString('Dot2', trim($crawler->filter('span[class="badge bg-primary"]')->text()));
    $this->assertStringContainsString('Card Title 1', trim($crawler->filter('h5.card-title')->text()));
    $link_element = $crawler->filter('h5.card-title a');
    $this->assertCount(1, $link_element);
    $this->assertStringContainsString(
      'http://www.example.com/',
      $link_element->attr('href')
    );
    $text_element = $crawler->filter('p.card-text.mb-2');
    $this->assertCount(1, $text_element);
    $this->assertStringContainsString(
      'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus ut ex tristique, dignissim sem ac, bibendum est. Sed vehicula lorem non nunc tincidunt hendrerit. Nunc tristique ante et fringilla fermentum.',
      $text_element->text()
    );
    $this->assertCount(0, $crawler->filter('span.d-md-inline.d-block.text-muted.mb-2.mb-md-0'));
    $this->assertCount(0, $crawler->filter('time[datetime="2011-11-13T12:00:00Z"]'));
    $this->assertCount(1, $crawler->filter('a.text-underline-hover'));

    // Variant - thumbnail_primary / Date - No / Description - No .
    $paragraph->get('oe_paragraphs_variant')->setValue('thumbnail_primary');
    $paragraph->get('field_oe_text_long')->setValue('');
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('div.card'));
    $image_element = $crawler->filter('.card-img-top');
    $this->assertCount(1, $image_element);
    $this->assertStringContainsString(
      file_url_transform_relative(file_create_url($en_file->getFileUri())),
      $image_element->attr('src')
    );
    $this->assertCount(1, $crawler->filter('div.card-body'));
    $this->assertCount(1, $crawler->filter('h5.card-title'));
    $this->assertStringContainsString('Dot1', trim($crawler->filter('span[class="me-2 badge bg-primary"]')->text()));
    $this->assertStringContainsString('Dot2', trim($crawler->filter('span[class="badge bg-primary"]')->text()));
    $this->assertStringContainsString('Card Title 1', trim($crawler->filter('h5.card-title')->text()));
    $link_element = $crawler->filter('h5.card-title a');
    $this->assertCount(1, $link_element);
    $this->assertStringContainsString(
      'http://www.example.com/',
      $link_element->attr('href')
    );
    $text_element = $crawler->filter('p.card-text.mb-3');
    $this->assertCount(1, $text_element);
    $this->assertStringContainsString(
      '',
      $text_element->text()
    );
    $this->assertCount(1, $crawler->filter('a.text-underline-hover'));

    // Variant - thumbnail_secondary / Date - No.
    $paragraph->get('oe_paragraphs_variant')->setValue('thumbnail_secondary');
    $paragraph->get('field_oe_text_long')->setValue('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus ut ex tristique, dignissim sem ac, bibendum est. Sed vehicula lorem non nunc tincidunt hendrerit. Nunc tristique ante et fringilla fermentum.');
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('div.card'));
    $image_element = $crawler->filter('.card-img-bottom');
    $this->assertCount(1, $image_element);
    $this->assertStringContainsString(
      file_url_transform_relative(file_create_url($en_file->getFileUri())),
      $image_element->attr('src')
    );
    $this->assertCount(1, $crawler->filter('div.card-body'));
    $this->assertCount(1, $crawler->filter('h5.card-title'));
    $this->assertStringContainsString('Dot1', trim($crawler->filter('span[class="me-2 badge bg-primary"]')->text()));
    $this->assertStringContainsString('Dot2', trim($crawler->filter('span[class="badge bg-primary"]')->text()));
    $this->assertStringContainsString('Card Title 1', trim($crawler->filter('h5.card-title')->text()));
    $link_element = $crawler->filter('h5.card-title a');
    $this->assertCount(1, $link_element);
    $this->assertStringContainsString(
      'http://www.example.com/',
      $link_element->attr('href')
    );
    $text_element = $crawler->filter('p.card-text.mb-3');
    $this->assertCount(1, $text_element);
    $this->assertStringContainsString(
      'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus ut ex tristique, dignissim sem ac, bibendum est. Sed vehicula lorem non nunc tincidunt hendrerit. Nunc tristique ante et fringilla fermentum.',
      $text_element->text()
    );
    $this->assertCount(1, $crawler->filter('a.text-underline-hover'));

    // Variant - default with internal link.
    $this->createContentType([
      'type' => 'article',
      'name' => 'Article',
    ]);

    $node = $this->createNode([
      'created' => 1636977600,
      'type' => 'article',
    ]);
    $nid = $node->id();
    $paragraph->get('field_oe_link')->setValue([
      'uri' => 'internal:/node/' . $nid,
      'title' => $node->getTitle(),
    ]);

    $paragraph->get('field_oe_image')->setValue([
      'title' => 'Example Image',
      'alt' => 'Alt for image',
      'target_id' => $en_file->id(),
    ]);
    $paragraph->get('oe_paragraphs_variant')->setValue('default');
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('div.card'));
    $image_element = $crawler->filter('.card-img-top');
    $this->assertCount(1, $image_element);
    $this->assertStringContainsString(
      file_url_transform_relative(file_create_url($en_file->getFileUri())),
      $image_element->attr('src')
    );
    $this->assertCount(1, $crawler->filter('div.card-body'));
    $this->assertCount(1, $crawler->filter('h5.card-title'));
    $this->assertStringContainsString('Dot1', trim($crawler->filter('span[class="me-2 badge bg-primary"]')->text()));
    $this->assertStringContainsString('Dot2', trim($crawler->filter('span[class="badge bg-primary"]')->text()));
    $this->assertStringContainsString('Card Title 1', trim($crawler->filter('h5.card-title')->text()));
    $link_element = $crawler->filter('h5.card-title a');
    $this->assertCount(1, $link_element);
    $this->assertStringContainsString(
      '/node/1',
      $link_element->attr('href')
    );
    $text_element = $crawler->filter('p.card-text.mb-3');
    $this->assertCount(1, $text_element);
    $this->assertStringContainsString(
      'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus ut ex tristique, dignissim sem ac, bibendum est. Sed vehicula lorem non nunc tincidunt hendrerit. Nunc tristique ante et fringilla fermentum.',
      $text_element->text()
    );
    $this->assertStringContainsString('Article', trim($crawler->filter('span[class="text-muted d-md-inline d-block me-4 mb-2 mb-md-0"]')->text()));
    $this->assertStringContainsString('15 November 2021', trim($crawler->filter('span[class="d-md-inline d-block text-muted mb-2 mb-md-0"]')->text()));
    $this->assertCount(1, $crawler->filter('a.text-underline-hover'));
  }

}
