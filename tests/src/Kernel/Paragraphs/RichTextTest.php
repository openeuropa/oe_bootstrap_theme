<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel\Paragraphs;

use Drupal\paragraphs\Entity\Paragraph;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the "Rich text" paragraphs.
 */
class RichTextTest extends ParagraphsTestBase {

  /**
   * Tests the rendering of the paragraph type.
   */
  public function testRendering(): void {
    // Create Rich text paragraph.
    $text_original = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque ornare et elit a dictum. Maecenas lacinia eros quis eros iaculis, sit amet bibendum massa facilisis. Integer arcu nisl, fringilla nec quam vel, tincidunt maximus ex. Suspendisse ac arcu efficitur, feugiat tellus vel, viverra sapien. Etiam vitae condimentum lorem. Nulla congue ligula lacinia efficitur tempus. Duis vitae auctor enim. Nulla iaculis, diam et sagittis scelerisque, est mauris luctus sem, a imperdiet lacus diam eu dui. Morbi accumsan, augue eu gravida elementum, libero mi blandit odio, eu fringilla nunc ipsum non tellus. Suspendisse dapibus elit at lobortis pretium. Quisque vestibulum ut purus sit amet molestie. Sed eget volutpat justo, vel varius augue. Vestibulum vel risus facilisis, feugiat sem aliquam, lobortis ante.';
    $paragraph = Paragraph::create([
      'type' => 'oe_rich_text',
      'field_oe_title' => 'Rich text example',
      'field_oe_text_long' => $text_original,
    ]);
    $paragraph->save();

    // Testing: Rich text.
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $title = $crawler->filter('h2');
    $this->assertCount(1, $title);
    $this->assertStringContainsString(
      'Rich text example',
      $title->html()
    );

    $text = $crawler->filter('p');
    $this->assertCount(1, $text);
    $this->assertStringContainsString(
      $text_original,
      $text->html()
    );
  }

}
