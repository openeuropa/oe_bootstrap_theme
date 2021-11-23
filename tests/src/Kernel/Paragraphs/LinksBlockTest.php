<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel\Paragraphs;

use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Tests\oe_bootstrap_theme\Kernel\PatternAssertions\LinksBlockAssertion;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the "Social media follow" paragraphs.
 */
class LinksBlockTest extends LinksBlockAssertion {

  /**
   * Tests the rendering of the paragraph type.
   */
  public function testRendering(): void {
    // Create Links Block paragraph.
    $paragraph = Paragraph::create([
      'type' => 'oe_links_block',
      'field_oe_text' => 'More information',
      'oe_bt_links_block_direction' => 'vertical',
      'oe_bt_links_block_background' => 'gray',
      'field_oe_links' => $this->getBlockLinks(),
    ]);
    $paragraph->save();

    // Testing: LinksBlock vertical gray.
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertBackgroundGray($crawler);
    $this->assertLinksBlockRendering($crawler);
    $this->assertVerticalLinks($crawler);

    // Testing: LinksBlock horizontal gray.
    $paragraph->get('oe_bt_links_block_direction')->setValue('horizontal');
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertBackgroundGray($crawler);
    $this->assertLinksBlockRendering($crawler);
    $this->assertHorizontalLinks($crawler, FALSE);

    // Testing: LinksBlock vertical transparent.
    $paragraph->get('oe_bt_links_block_direction')->setValue('vertical');
    $paragraph->get('oe_bt_links_block_background')->setValue('transparent');
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertBackgroundTransparent($crawler);
    $this->assertLinksBlockRendering($crawler);
    $this->assertVerticalLinks($crawler);

    // Testing: LinksBlock horizontal transparent.
    $paragraph->get('oe_bt_links_block_direction')->setValue('horizontal');
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertBackgroundTransparent($crawler);
    $this->assertLinksBlockRendering($crawler);
    $this->assertHorizontalLinks($crawler, FALSE);
  }

}
