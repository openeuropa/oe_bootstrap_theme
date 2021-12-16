<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel\Paragraphs;

use Symfony\Component\DomCrawler\Crawler;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Tests the "Facts and Figures" paragraphs.
 */
class FactsFiguresTest extends ParagraphsTestBase {

  /**
   * Tests the rendering of the paragraph type.
   */
  public function testRendering(): void {
    // Create Fact paragraphs.
    $paragraph_fact =
    [
      0 => Paragraph::create([
        'type' => 'oe_fact',
        'field_oe_icon' => 'box-arrow-up',
        'field_oe_title' => '1529 JIRA Ticket',
        'field_oe_subtitle' => 'Jira Tickets',
        'field_oe_plain_text_long' => 'Nunc condimentum sapien ut nibh finibus suscipit vitae at justo. Morbi quis odio faucibus, commodo tortor id, elementum libero.',
      ]),
      1 => Paragraph::create([
        'type' => 'oe_fact',
        'field_oe_icon' => 'box-arrow-up',
        'field_oe_title' => '337 Features',
        'field_oe_subtitle' => 'Feature tickets',
        'field_oe_plain_text_long' => 'Turpis varius congue venenatis, erat dui feugiat felis.',
      ]),
      2 => Paragraph::create([
        'type' => 'oe_fact',
        'field_oe_icon' => 'box-arrow-up',
        'field_oe_title' => '107 Tests',
        'field_oe_subtitle' => 'Test tickets',
        'field_oe_plain_text_long' => 'Cras vestibulum efficitur mi, quis porta tellus rutrum ut. Quisque at pulvinar sem.',
      ]),
      3 => Paragraph::create([
        'type' => 'oe_fact',
        'field_oe_icon' => 'box-arrow-up',
        'field_oe_title' => '5670 Variants',
        'field_oe_subtitle' => 'Test variants',
        'field_oe_plain_text_long' => 'Aliquam lacinia diam eu sem malesuada, in interdum ante bibendum.',
      ]),
      4 => Paragraph::create([
        'type' => 'oe_fact',
        'field_oe_icon' => 'box-arrow-up',
        'field_oe_title' => '345 Dev Ticket',
        'field_oe_subtitle' => 'Jira ticket',
        'field_oe_plain_text_long' => 'Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Duis nec lectus tortor.',
      ]),
      5 => Paragraph::create([
        'type' => 'oe_fact',
        'field_oe_icon' => 'box-arrow-up',
        'field_oe_title' => '43 Components',
        'field_oe_subtitle' => 'Figma components',
        'field_oe_plain_text_long' => 'Sed efficitur bibendum rutrum. Nunc feugiat congue augue ac consectetur.',
      ]),
    ];
    // Create Facts and figures paragraph.
    $paragraph = Paragraph::create([
      'type' => 'oe_facts_figures',
      'field_oe_title' => 'Fact and figures block',
      'field_oe_link' => [
        'uri' => 'https://www.readmore.com',
        'title' => 'Read more',
      ],
      'oe_bt_layout' => 2,
      'oe_bt_facts_figures_background' => 'default',
      'field_oe_paragraphs' => $paragraph_fact,
    ]);
    $paragraph->save();

    // Testing: Facts and figures - Default layout.
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('div.bcl-fact-figures.bcl-fact-figures--default'));
    $this->assertCount(6, $crawler->filter('h3.text-capitalize.fw-bold'));
    $this->assertCount(6, $crawler->filter('h5.fw-bold'));
    $this->assertCount(1, $crawler->filter('div.row-cols-md-3.row.row-cols-1.row-cols-sm-2.g-4.mt-3'));
    $this->assertCount(6, $crawler->filter('svg.bi.icon--l'));
    $this->assertCount(6, $crawler->filter('h3.text-capitalize.fw-bold.mt-2'));
    $this->assertCount(6, $crawler->filter('h5.fw-bold'));
    $this->assertCount(6, $crawler->filter('div.col.h-100'));

    $link = $crawler->filter('a[href="https://www.readmore.com"]');
    $this->assertStringContainsString(
      'Read more',
      $link->html()
    );

    $title_fact = $crawler->filter('div.row-cols-md-3.row.row-cols-1.row-cols-sm-2.g-4.mt-3 > div.col.h-100:nth-child(1) > h3.text-capitalize.fw-bold');
    $this->assertStringContainsString(
      '1529 JIRA Ticket',
      $title_fact->html()
    );
    $subtitle_fact = $crawler->filter('div.row-cols-md-3.row.row-cols-1.row-cols-sm-2.g-4.mt-3 > div.col.h-100:nth-child(1) >  h5.fw-bold');
    $this->assertStringContainsString(
      'Jira Tickets',
      $subtitle_fact->html()
    );
    $description_fact = $crawler->filter('div.row-cols-md-3.row.row-cols-1.row-cols-sm-2.g-4.mt-3 > div.col.h-100:nth-child(1) > p');
    $this->assertStringContainsString(
      'Nunc condimentum sapien ut nibh finibus suscipit vitae at justo. Morbi quis odio faucibus, commodo tortor id, elementum libero.',
      $description_fact->html()
    );

    $title_fact = $crawler->filter('div.row-cols-md-3.row.row-cols-1.row-cols-sm-2.g-4.mt-3 > div.col.h-100:nth-child(2) > h3.text-capitalize.fw-bold');
    $this->assertStringContainsString(
      '337 Features',
      $title_fact->html()
    );
    $subtitle_fact = $crawler->filter('div.row-cols-md-3.row.row-cols-1.row-cols-sm-2.g-4.mt-3 > div.col.h-100:nth-child(2) >  h5.fw-bold');
    $this->assertStringContainsString(
      'Feature tickets',
      $subtitle_fact->html()
    );
    $description_fact = $crawler->filter('div.row-cols-md-3.row.row-cols-1.row-cols-sm-2.g-4.mt-3 > div.col.h-100:nth-child(2) > p');
    $this->assertStringContainsString(
      'Turpis varius congue venenatis, erat dui feugiat felis.',
      $description_fact->html()
    );

    $title_fact = $crawler->filter('div.row-cols-md-3.row.row-cols-1.row-cols-sm-2.g-4.mt-3 > div.col.h-100:nth-child(3) > h3.text-capitalize.fw-bold');
    $this->assertStringContainsString(
      '107 Tests',
      $title_fact->html()
    );
    $subtitle_fact = $crawler->filter('div.row-cols-md-3.row.row-cols-1.row-cols-sm-2.g-4.mt-3 > div.col.h-100:nth-child(3) >  h5.fw-bold');
    $this->assertStringContainsString(
      'Test tickets',
      $subtitle_fact->html()
    );
    $description_fact = $crawler->filter('div.row-cols-md-3.row.row-cols-1.row-cols-sm-2.g-4.mt-3 > div.col.h-100:nth-child(3) > p');
    $this->assertStringContainsString(
      'Cras vestibulum efficitur mi, quis porta tellus rutrum ut. Quisque at pulvinar sem.',
      $description_fact->html()
    );

    $title_fact = $crawler->filter('div.row-cols-md-3.row.row-cols-1.row-cols-sm-2.g-4.mt-3 > div.col.h-100:nth-child(4) > h3.text-capitalize.fw-bold');
    $this->assertStringContainsString(
      '5670 Variants',
      $title_fact->html()
    );
    $subtitle_fact = $crawler->filter('div.row-cols-md-3.row.row-cols-1.row-cols-sm-2.g-4.mt-3 > div.col.h-100:nth-child(4) >  h5.fw-bold');
    $this->assertStringContainsString(
      'Test variants',
      $subtitle_fact->html()
    );
    $description_fact = $crawler->filter('div.row-cols-md-3.row.row-cols-1.row-cols-sm-2.g-4.mt-3 > div.col.h-100:nth-child(4) > p');
    $this->assertStringContainsString(
      'Aliquam lacinia diam eu sem malesuada, in interdum ante bibendum.',
      $description_fact->html()
    );

    $title_fact = $crawler->filter('div.row-cols-md-3.row.row-cols-1.row-cols-sm-2.g-4.mt-3 > div.col.h-100:nth-child(5) > h3.text-capitalize.fw-bold');
    $this->assertStringContainsString(
      '345 Dev Ticket',
      $title_fact->html()
    );
    $subtitle_fact = $crawler->filter('div.row-cols-md-3.row.row-cols-1.row-cols-sm-2.g-4.mt-3 > div.col.h-100:nth-child(5) >  h5.fw-bold');
    $this->assertStringContainsString(
      'Jira ticket',
      $subtitle_fact->html()
    );
    $description_fact = $crawler->filter('div.row-cols-md-3.row.row-cols-1.row-cols-sm-2.g-4.mt-3 > div.col.h-100:nth-child(5) > p');
    $this->assertStringContainsString(
      'Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Duis nec lectus tortor.',
      $description_fact->html()
    );

    $title_fact = $crawler->filter('div.row-cols-md-3.row.row-cols-1.row-cols-sm-2.g-4.mt-3 > div.col.h-100:nth-child(6) > h3.text-capitalize.fw-bold');
    $this->assertStringContainsString(
      '43 Components',
      $title_fact->html()
    );
    $subtitle_fact = $crawler->filter('div.row-cols-md-3.row.row-cols-1.row-cols-sm-2.g-4.mt-3 > div.col.h-100:nth-child(6) >  h5.fw-bold');
    $this->assertStringContainsString(
      'Figma components',
      $subtitle_fact->html()
    );
    $description_fact = $crawler->filter('div.row-cols-md-3.row.row-cols-1.row-cols-sm-2.g-4.mt-3 > div.col.h-100:nth-child(6) > p');
    $this->assertStringContainsString(
      'Sed efficitur bibendum rutrum. Nunc feugiat congue augue ac consectetur.',
      $description_fact->html()
    );
  }

}
