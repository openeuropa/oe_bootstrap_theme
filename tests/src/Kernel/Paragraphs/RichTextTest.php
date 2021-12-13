<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel\Paragraphs;

use Drupal\filter\Entity\FilterFormat;
use Drupal\paragraphs\Entity\Paragraph;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the "Rich text" paragraphs.
 */
class RichTextTest extends ParagraphsTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->filterFormat = FilterFormat::create([
      'format' => 'filtered_html',
      'name' => 'Filtered HTML',
      'weight' => 0,
    ]);
    $this->filterFormat->save();
  }

  /**
   * Tests the rendering of the paragraph type.
   */
  public function testRendering(): void {
    $text_original = '<p id="paragraph-1">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque ornare et elit a dictum. Maecenas lacinia eros quis eros iaculis, sit amet bibendum massa facilisis. Integer arcu nisl, fringilla nec quam vel, tincidunt maximus ex. Suspendisse ac arcu efficitur, feugiat tellus vel, viverra sapien. Etiam vitae condimentum lorem. Nulla congue ligula lacinia efficitur tempus. Duis vitae auctor enim. Nulla iaculis, diam et sagittis scelerisque, est mauris luctus sem, a imperdiet lacus diam eu dui. Morbi accumsan, augue eu gravida elementum, libero mi blandit odio, eu fringilla nunc ipsum non tellus. Suspendisse dapibus elit at lobortis pretium. Quisque vestibulum ut purus sit amet molestie. Sed eget volutpat justo, vel varius augue. Vestibulum vel risus facilisis, feugiat sem aliquam, lobortis ante.</p><p id="paragraph-2"><strong>Bold</strong></p><p id="paragraph-3"><em>Italic</em></p><p id="paragraph-4"><a href="https://www.example-1.com">Link</a></p><p id="paragraph-5">List:</p><ul><li>option a</li><li">option b</li></ul><p id="paragraph-6">Numbered list:</p><ol><li>first option</li><li>second option</li></ol><p id="paragraph-7">Block quote:</p><blockquote><p>I am a block quote</p></blockquote>';
    $paragraph = Paragraph::create([
      'type' => 'oe_rich_text',
      'field_oe_title' => 'Rich text example',
      'field_oe_text_long' => [
        'value' => $text_original,
        'format' => 'filtered_html',
      ],
    ]);
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $title = $crawler->filter('h4');
    $this->assertCount(1, $title);
    $this->assertStringContainsString(
      'Rich text example',
      $title->html()
    );
    // Simple text.
    $text = $crawler->filter('p#paragraph-1');
    $this->assertCount(1, $text);
    $this->assertStringContainsString(
      'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque ornare et elit a dictum. Maecenas lacinia eros quis eros iaculis, sit amet bibendum massa facilisis. Integer arcu nisl, fringilla nec quam vel, tincidunt maximus ex. Suspendisse ac arcu efficitur, feugiat tellus vel, viverra sapien. Etiam vitae condimentum lorem. Nulla congue ligula lacinia efficitur tempus. Duis vitae auctor enim. Nulla iaculis, diam et sagittis scelerisque, est mauris luctus sem, a imperdiet lacus diam eu dui. Morbi accumsan, augue eu gravida elementum, libero mi blandit odio, eu fringilla nunc ipsum non tellus. Suspendisse dapibus elit at lobortis pretium. Quisque vestibulum ut purus sit amet molestie. Sed eget volutpat justo, vel varius augue. Vestibulum vel risus facilisis, feugiat sem aliquam, lobortis ante.',
      $text->html()
    );
    // Bold format.
    $text = $crawler->filter('p#paragraph-2 strong');
    $this->assertCount(1, $text);
    $this->assertStringContainsString(
      'Bold',
      $text->html()
    );
    // Italic.
    $text = $crawler->filter('p#paragraph-3 em');
    $this->assertCount(1, $text);
    $this->assertStringContainsString(
      'Italic',
      $text->html()
    );
    // Link.
    $text = $crawler->filter('p#paragraph-4 a');
    $this->assertCount(1, $text);
    $this->assertStringContainsString(
      'Link',
      $text->html()
    );
    $this->assertStringContainsString(
      'https://www.example-1.com',
      $text->attr('href')
    );
    // Unordered list.
    $text = $crawler->filter('ul li:nth-child(1)');
    $this->assertCount(1, $text);
    $this->assertStringContainsString(
      'option a',
      $text->html()
    );
    $text = $crawler->filter('ul li:nth-child(2)');
    $this->assertCount(1, $text);
    $this->assertStringContainsString(
      'option b',
      $text->html()
    );
    // Ordered list.
    $text = $crawler->filter('ol li:nth-child(1)');
    $this->assertCount(1, $text);
    $this->assertStringContainsString(
      'first option',
      $text->html()
    );
    $text = $crawler->filter('ol li:nth-child(2)');
    $this->assertCount(1, $text);
    $this->assertStringContainsString(
      'second option',
      $text->html()
    );
    // Blcokquote.
    $text = $crawler->filter('blockquote p');
    $this->assertCount(1, $text);
    $this->assertStringContainsString(
      'I am a block quote',
      $text->html()
    );
  }

}
