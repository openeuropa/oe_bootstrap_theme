<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel\Paragraphs;

use Drupal\paragraphs\Entity\Paragraph;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the "Description list" paragraphs.
 */
class DescriptionListTest extends ParagraphsTestBase {

  /**
   * Tests the rendering of the paragraph type.
   */
  public function testRendering(): void {
    // Create Description list paragraph with horizontal variant.
    $paragraph = Paragraph::create([
      'type' => 'oe_description_list',
      'field_oe_title' => 'Description list paragraph',
      'oe_bt_orientation' => 'horizontal',
      'field_oe_description_list_items' => [
        0 => [
          'term' => 'Aliquam ultricies',
          'description' => 'Donec et leo ac velit posuere tempor mattis ac mi. Vivamus nec dictum lectus. Aliquam ultricies placerat eros, vitae ornare sem.',
        ],
        1 => [
          'term' => 'Etiam lacinia',
          'description' => 'Quisque tempor sollicitudin lacinia. Morbi imperdiet nulla et nunc aliquet, vel lobortis nunc cursus. Mauris vitae hendrerit felis.',
        ],
      ],
    ]);
    $paragraph->save();

    // Testing: Description list paragraph with horizontal variant.
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('h4'));
    $this->assertCount(1, $crawler->filter('dl.d-md-grid.grid-3-9'));
    $this->assertCount(2, $crawler->filter('dd'));
    $this->assertCount(2, $crawler->filter('dt div.align-middle.d-inline-block'));

    $title = $crawler->filter('h4.fw-bold.mb-4');
    $this->assertStringContainsString(
      'Description list paragraph',
      $title->html()
    );

    $term_1 = $crawler->filter('dl > div:nth-child(1) > dt > div');
    $this->assertStringContainsString(
      'Aliquam ultricies',
      $term_1->html()
    );
    $description_1 = $crawler->filter('dl > dd:nth-child(2)');
    $this->assertStringContainsString(
      'Donec et leo ac velit posuere tempor mattis ac mi. Vivamus nec dictum lectus. Aliquam ultricies placerat eros, vitae ornare sem.',
      $description_1->html()
    );
    $term_2 = $crawler->filter('dl > div:nth-child(3) > dt > div');
    $this->assertStringContainsString(
      'Etiam lacinia',
      $term_2->html()
    );
    $description_2 = $crawler->filter('dl > dd:nth-child(4)');
    $this->assertStringContainsString(
      'Quisque tempor sollicitudin lacinia. Morbi imperdiet nulla et nunc aliquet, vel lobortis nunc cursus. Mauris vitae hendrerit felis.',
      $description_2->html()
    );

    // Testing: Description list paragraph with vertival variant.
    $paragraph->get('oe_bt_orientation')->setValue('vertical');
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $title = $crawler->filter('h4.fw-bold.mb-4');
    $this->assertStringContainsString(
      'Description list paragraph',
      $title->html()
    );

    $term_1 = $crawler->filter('dl > dt:nth-child(1) > div');
    $this->assertStringContainsString(
      'Aliquam ultricies',
      $term_1->html()
    );
    $description_1 = $crawler->filter('dl > dd:nth-child(2)');
    $this->assertStringContainsString(
      'Donec et leo ac velit posuere tempor mattis ac mi. Vivamus nec dictum lectus. Aliquam ultricies placerat eros, vitae ornare sem.',
      $description_1->html()
    );
    $term_2 = $crawler->filter('dl > dt:nth-child(3) > div');
    $this->assertStringContainsString(
      'Etiam lacinia',
      $term_2->html()
    );
    $description_2 = $crawler->filter('dl > dd:nth-child(4)');
    $this->assertStringContainsString(
      'Quisque tempor sollicitudin lacinia. Morbi imperdiet nulla et nunc aliquet, vel lobortis nunc cursus. Mauris vitae hendrerit felis.',
      $description_2->html()
    );
  }

}
