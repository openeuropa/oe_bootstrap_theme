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
    $this->assertEquals('Description list paragraph', $title->text());

    $term_1 = $crawler->filter('dl > div:nth-child(1) > dt');
    $this->assertEquals('Aliquam ultricies', $term_1->text());
    $description_1 = $crawler->filter('dl > div:nth-child(1) + dd');
    $this->assertEquals(
      'Donec et leo ac velit posuere tempor mattis ac mi. Vivamus nec dictum lectus. Aliquam ultricies placerat eros, vitae ornare sem.',
      $description_1->text()
    );

    $term_2 = $crawler->filter('dl > div:nth-child(3) > dt');
    $this->assertEquals('Etiam lacinia', $term_2->text());
    $description_2 = $crawler->filter('dl > div:nth-child(3) + dd');
    $this->assertEquals(
      'Quisque tempor sollicitudin lacinia. Morbi imperdiet nulla et nunc aliquet, vel lobortis nunc cursus. Mauris vitae hendrerit felis.',
      $description_2->text()
    );

    // Testing: Description list paragraph with vertical variant.
    $paragraph->get('oe_bt_orientation')->setValue('vertical');
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $title = $crawler->filter('h4.fw-bold.mb-4');
    $this->assertEquals('Description list paragraph', $title->text());

    $term_1 = $crawler->filter('dl > dt:nth-child(1)');
    $this->assertEquals('Aliquam ultricies', $term_1->text());
    $description_1 = $crawler->filter('dl > dt:nth-child(1) + dd');
    $this->assertEquals(
      'Donec et leo ac velit posuere tempor mattis ac mi. Vivamus nec dictum lectus. Aliquam ultricies placerat eros, vitae ornare sem.',
      $description_1->text()
    );

    $term_2 = $crawler->filter('dl > dt:nth-child(3)');
    $this->assertEquals('Etiam lacinia', $term_2->text());
    $description_2 = $crawler->filter('dl > dt:nth-child(3) + dd');
    $this->assertEquals(
      'Quisque tempor sollicitudin lacinia. Morbi imperdiet nulla et nunc aliquet, vel lobortis nunc cursus. Mauris vitae hendrerit felis.',
      $description_2->text()
    );
  }

}
