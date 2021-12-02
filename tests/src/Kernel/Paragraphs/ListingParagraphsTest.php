<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel\Paragraphs;

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Tests\oe_bootstrap_theme\Kernel\PatternAssertions\ListingAssertion;
use Symfony\Component\DomCrawler\Crawler;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\file\Entity\File;

/**
 * Tests the rendering of paragraph Listing.
 */
class ListingParagraphsTest extends ListingAssertion {

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
   * Test List Items Block paragraph rendering.
   */
  public function testListing(): void {
    $image_file = file_save_data(file_get_contents(drupal_get_path('theme', 'oe_bootstrap_theme') . '/tests/fixtures/arch.jpeg'), 'public://arch_en.jpeg');
    $image_file->setPermanent();
    $image_file->save();

    $this->createContentType([
      'type' => 'article',
      'name' => 'Article',
    ]);

    $node = $this->createNode([
      'created' => 1636977600,
      'type' => 'article',
    ]);
    $nid = (int) $node->id();

    $paragraph_storage = $this->container->get('entity_type.manager')->getStorage('paragraph');
    $paragraph = $paragraph_storage->create([
      'type' => 'oe_list_item_block',
      'oe_paragraphs_variant' => 'default',
      'field_oe_list_item_block_layout' => 'one_column',
      'field_oe_title' => 'Listing item block title',
      'field_oe_paragraphs' => $this->createListItems($image_file, $node),
    ]);
    $paragraph->save();

    // Testing Default 1 col.
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertListingRendering($crawler, $nid);
    $this->assertDefaultListingRendering($crawler, $image_file);
    $this->assertCount(1, $crawler->filter('div.bcl-listing--default-1-col'));
    $this->assertCount(1, $crawler->filter('div.row.row-cols-1'));
    $this->assertCount(6, $crawler->filter('div.col-md-3.col-lg-2.rounded'));
    $this->assertCount(6, $crawler->filter('div.col-md-9.col-lg-10'));

    // Testing Default 2 col.
    $paragraph->get('field_oe_list_item_block_layout')->setValue('two_columns');
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertListingRendering($crawler, $nid);
    $this->assertDefaultListingRendering($crawler, $image_file);
    $this->assertCount(1, $crawler->filter('div.bcl-listing--default-2-col'));
    $this->assertCount(1, $crawler->filter('div.row.row-cols-1.row-cols-md-2'));
    $this->assertCount(6, $crawler->filter('div.col-xl-3.col-md-5'));
    $this->assertCount(6, $crawler->filter('div.col-xl-9.col-md-7'));

    // Testing Default 3 col.
    $paragraph->get('field_oe_list_item_block_layout')->setValue('three_columns');
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertListingRendering($crawler, $nid);
    $this->assertDefaultListingRendering($crawler, $image_file);
    $this->assertCount(1, $crawler->filter('div.bcl-listing--default-3-col'));
    $this->assertCount(1, $crawler->filter('div.row.row-cols-1.row-cols-md-2.row-cols-xl-3'));
    $this->assertCount(6, $crawler->filter('div.col-lg-4.col-md-6'));
    $this->assertCount(6, $crawler->filter('div.col-lg-8.col-md-6'));

    // Testing Highlight 1 col.
    $paragraph->get('oe_paragraphs_variant')->setValue('highlight');
    $paragraph->get('field_oe_list_item_block_layout')->setValue('one_column');
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertListingRendering($crawler, $nid);
    $this->assertHighlightListingRendering($crawler, $image_file);
    $this->assertCount(1, $crawler->filter('div.bcl-listing--highlight-1-col'));
    $this->assertCount(1, $crawler->filter('div.row.row-cols-1'));
    $this->assertCount(6, $crawler->filter('div.col.mt-4-5'));
    $this->assertCount(6, $crawler->filter('div.card-body.pb-4'));

    // Testing Highlight 2 col.
    $paragraph->get('field_oe_list_item_block_layout')->setValue('two_columns');
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertListingRendering($crawler, $nid);
    $this->assertHighlightListingRendering($crawler, $image_file);
    $this->assertCount(1, $crawler->filter('div.bcl-listing--highlight-2-col'));
    $this->assertCount(1, $crawler->filter('div.row.row-cols-1.row-cols-md-2'));
    $this->assertCount(6, $crawler->filter('div.listing-item--highlight.h-100.rounded-2'));
    $this->assertCount(6, $crawler->filter('div.card-body.pt-0'));

    // Testing Highlight 3 col.
    $paragraph->get('field_oe_list_item_block_layout')->setValue('three_columns');
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertListingRendering($crawler, $nid);
    $this->assertHighlightListingRendering($crawler, $image_file);
    $this->assertCount(1, $crawler->filter('div.bcl-listing--highlight-3-col'));
    $this->assertCount(1, $crawler->filter('div.row.row-cols-1.row-cols-md-3'));
    $this->assertCount(6, $crawler->filter('div.listing-item--highlight.h-100.rounded-2'));
    $this->assertCount(6, $crawler->filter('div.card-body.pt-0'));

    // Testing Date 1 col.
    $paragraph->get('oe_paragraphs_variant')->setValue('date');
    $paragraph->get('field_oe_list_item_block_layout')->setValue('one_column');
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertListingRendering($crawler, $nid);
    $this->assertDateListingRendering($crawler);
    $this->assertCount(1, $crawler->filter('div.bcl-listing--default-1-col'));
    $this->assertCount(1, $crawler->filter('div.row.row-cols-1'));
    $this->assertCount(6, $crawler->filter('div.col-md-3.col-lg-2.rounded'));
    $this->assertCount(6, $crawler->filter('div.col-md-9.col-lg-10'));

    // Testing Date 2 col.
    $paragraph->get('field_oe_list_item_block_layout')->setValue('two_columns');
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertListingRendering($crawler, $nid);
    $this->assertDateListingRendering($crawler);
    $this->assertCount(1, $crawler->filter('div.bcl-listing--default-2-col'));
    $this->assertCount(1, $crawler->filter('div.row.row-cols-1.row-cols-md-2'));
    $this->assertCount(6, $crawler->filter('div.col-xl-3.col-md-5'));
    $this->assertCount(6, $crawler->filter('div.col-xl-9.col-md-7'));

    // Testing Date 3 col.
    $paragraph->get('field_oe_list_item_block_layout')->setValue('three_columns');
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertListingRendering($crawler, $nid);
    $this->assertDateListingRendering($crawler);
    $this->assertCount(1, $crawler->filter('div.bcl-listing--default-3-col'));
    $this->assertCount(1, $crawler->filter('div.row.row-cols-1.row-cols-md-2.row-cols-xl-3'));
    $this->assertCount(6, $crawler->filter('div.col-lg-4.col-md-6'));
    $this->assertCount(6, $crawler->filter('div.col-lg-8.col-md-6'));
  }

  /**
   * Assert default variant of Listing is rendering correctly.
   *
   * @param \Drupal\file\Entity\File $image_file
   *   Image file to be added to the list item.
   * @param \Drupal\node\Entity\Node $node
   *   A Node entity.
   */
  protected function createListItems(File $image_file, Node $node): array {
    $items = [];
    for ($i = 1; $i <= 6; $i++) {
      $paragraph = Paragraph::create([
        'type' => 'oe_list_item',
        'oe_paragraphs_variant' => 'default',
        'field_oe_title' => 'Item title ' . $i,
        'field_oe_text_long' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus ut ex tristique, dignissim sem ac, bibendum est. ' . $i,
        'field_oe_link' => [
          'uri' => 'entity:node/' . $node->id(),
          'title' => 'Example ' . $i,
        ],
        'field_oe_image' => [
          'alt' => 'Alt for image ' . $i,
          'target_id' => $image_file->id(),
        ],
        'field_oe_date' => '2011-11-13',
        'field_oe_meta' => [
          0 => [
            'value' => 'Label 1 - ' . $i,
          ],
          1 => [
            'value' => 'Label 2 - ' . $i,
          ],
        ],
      ]);
      $paragraph->save();
      $items[$i] = $paragraph;
    }

    return $items;
  }

}
