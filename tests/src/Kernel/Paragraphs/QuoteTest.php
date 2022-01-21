<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel\Paragraphs;

use Drupal\paragraphs\Entity\Paragraph;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the quote paragraphs.
 */
class QuoteTest extends ParagraphsTestBase {

  /**
   * Tests the rendering of the paragraph type.
   */
  public function testRendering(): void {
    $paragraph = Paragraph::create([
      'type' => 'oe_quote',
      'field_oe_plain_text_long' => 'Neque porro quisquam est qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit.',
      'field_oe_text' => 'Cicero',
    ]);
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('figure.text-left'));
    $quote = $crawler->filter('blockquote.blockquote');
    $this->assertCount(1, $quote);
    $this->assertEquals(
      'Neque porro quisquam est qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit.',
      trim($quote->text())
    );
    $caption = $crawler->filter('figcaption.blockquote-footer');
    $this->assertEquals('Cicero', trim($caption->text()));
  }

}
