<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel\Paragraphs;

use Drupal\paragraphs\Entity\Paragraph;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the "Content row" paragraph, "Inpage navigation" variant.
 */
class ContentRowInpageNavigationTest extends ParagraphsTestBase {

  /**
   * Tests the rendering of the paragraph type.
   */
  public function testRendering(): void {
    $paragraph_fact = [
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
    ];

    $paragraph_accordion = [
      0 => Paragraph::create([
        'type' => 'oe_accordion_item',
        'field_oe_icon' => 'box-arrow-up',
        'field_oe_text' => 'Accordion item 1',
        'field_oe_text_long' => 'Aenean at viverra tellus. Donec egestas ut ligula a condimentum. Cras sapien nulla, ornare eget lobortis vulputate, bibendum nec tellus. Fusce tristique diam quis mauris vehicula eleifend. Maecenas vitae luctus mi. Sed accumsan fermentum fermentum. Ut tristique quam at aliquam viverra. Suspendisse pulvinar risus tristique augue elementum, nec blandit sem mattis',
      ]),
      1 => Paragraph::create([
        'type' => 'oe_accordion_item',
        'field_oe_icon' => 'box-arrow-up',
        'field_oe_text' => 'Accordion item 2',
        'field_oe_text_long' => 'Morbi pretium efficitur dolor, a vulputate sem vulputate quis. Sed dictum massa eu nulla finibus, et porta dolor efficitur. Mauris pharetra dui sed consequat faucibus. Pellentesque felis nisi, fringilla non tortor ac, laoreet feugiat ante. Curabitur vel gravida augue. Nullam erat dui, viverra a arcu non, tincidunt pulvinar tortor. Maecenas non libero consequat massa ornare posuere. Quisque ultrices ullamcorper leo, non vulputate felis vestibulum a. Nam eu tellus enim.',
      ]),
      2 => Paragraph::create([
        'type' => 'oe_accordion_item',
        'field_oe_icon' => 'box-arrow-up',
        'field_oe_text' => 'Accordion item 3',
        'field_oe_text_long' => 'Mauris accumsan vulputate imperdiet. Aenean nec metus sem. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum maximus placerat orci, a placerat dolor iaculis vel. Nam pulvinar elementum odio ut tempor. In fermentum neque ut placerat rutrum.',
      ]),
    ];

    $sub_paragraphs = [
      0 => Paragraph::create([
        'type' => 'oe_rich_text',
        'field_oe_title' => 'Title rich text example 1',
        'field_oe_text_long' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque ornare et elit a dictum. Maecenas lacinia eros quis eros iaculis, sit amet bibendum massa facilisis. Integer arcu nisl, fringilla nec quam vel, tincidunt maximus ex. Suspendisse ac arcu efficitur, feugiat tellus vel, viverra sapien. Etiam vitae condimentum lorem. Nulla congue ligula lacinia efficitur tempus. Duis vitae auctor enim. Nulla iaculis, diam et sagittis scelerisque, est mauris luctus sem, a imperdiet lacus diam eu dui. Morbi accumsan, augue eu gravida elementum, libero mi blandit odio, eu fringilla nunc ipsum non tellus. Suspendisse dapibus elit at lobortis pretium. Quisque vestibulum ut purus sit amet molestie. Sed eget volutpat justo, vel varius augue. Vestibulum vel risus facilisis, feugiat sem aliquam, lobortis ante.',
      ]),
      1 => Paragraph::create([
        'type' => 'oe_links_block',
        'field_oe_text' => 'Links block example',
        'oe_bt_links_block_orientation' => 'vertical',
        'oe_bt_links_block_background' => 'gray',
        'field_oe_links' => [
          [
            'title' => 'Example 2',
            'uri' => 'https://example2.com',
          ],
          [
            'title' => 'Example 3',
            'uri' => 'https://example3.com',
          ],
          [
            'title' => 'Example 4',
            'uri' => 'https://example4.com',
          ],
        ],
      ]),
      2 => Paragraph::create([
        'type' => 'oe_facts_figures',
        'field_oe_title' => 'List item block example',
        'field_oe_link' => [
          'uri' => 'https://www.readmore.com',
          'title' => 'Read more',
        ],
        'field_oe_paragraphs' => $paragraph_fact,
        'field_oe_list_item_block_layout' => 3,
      ]),
      3 => Paragraph::create([
        'type' => 'oe_quote',
        'field_oe_text' => 'Quote 1',
        'field_oe_plain_text_long' => 'Maecenas id urna eleifend, elementum sapien vitae, semper massa. Curabitur mi leo, sagittis eget euismod egestas, ornare nec justo.',
      ]),
      4 => Paragraph::create([
        'type' => 'oe_social_media_follow',
        'field_oe_title' => 'Social media block',
        'field_oe_social_media_variant' => 'horizontal',
        'oe_bt_links_block_background' => 'gray',
        'field_oe_social_media_links' => [
          [
            'title' => 'Email',
            'uri' => 'mailto:example@com',
            'link_type' => 'email',
          ],
          [
            'title' => 'Facebook',
            'uri' => 'https://facebook.com',
            'link_type' => 'facebook',
          ],
        ],
        'field_oe_social_media_see_more' => [
          'title' => 'Other social networks',
          'uri' => 'https://europa.eu/european-union/contact/social-networks_en',
        ],
      ]),
      5 => Paragraph::create([
        'type' => 'oe_accordion',
        'field_oe_paragraphs' => $paragraph_accordion,
      ]),
    ];

    $paragraph = Paragraph::create([
      'type' => 'oe_content_row',
      'field_oe_title' => 'Page content',
      'field_oe_paragraphs' => $sub_paragraphs,
      'oe_paragraphs_variant' => 'inpage_navigation',
    ]);
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('div.row'));
    $this->assertCount(1, $crawler->filter('div.col-md-3.d-none.d-md-block'));
    $this->assertCount(1, $crawler->filter('nav.position-sticky'));
    $this->assertCount(3, $crawler->filter('h5'));
    $this->assertCount(1, $crawler->filter('h4'));
    $this->assertCount(1, $crawler->filter('ul.nav.nav-pills.flex-column'));
    $this->assertCount(6, $crawler->filter('li.nav-item'));
    $this->assertCount(6, $crawler->filter('a.nav-link'));
    $this->assertCount(1, $crawler->filter('div.col-md-9'));
    $this->assertCount(5, $crawler->filter('p'));

    $this->assertCount(1, $crawler->filter('div#bcl-inpage-item-1'));
    $title = $crawler->filter('div#bcl-inpage-item-1 h4');
    $this->assertStringContainsString(
      'Title rich text example 1',
      $title->html()
    );

    $link = $crawler->filter('a[href="#bcl-inpage-item-1"]');
    $this->assertCount(1, $link);
    $this->assertStringContainsString(
      'Title rich text example 1',
      $link->html()
    );

    $this->assertCount(1, $crawler->filter('div#bcl-inpage-item-2'));
    $title = $crawler->filter('div#bcl-inpage-item-2 h5');
    $this->assertStringContainsString(
      'Links block example',
      $title->html()
    );

    $link = $crawler->filter('a[href="#bcl-inpage-item-2"]');
    $this->assertCount(1, $link);
    $this->assertStringContainsString(
      'Links block example',
      $link->html()
    );

    $this->assertCount(1, $crawler->filter('div#bcl-inpage-item-6'));
    $title = $crawler->filter('div#bcl-inpage-item-6 div');
    $this->assertStringContainsString(
      'List item block example',
      $title->html()
    );

    $link = $crawler->filter('a[href="#bcl-inpage-item-6"]');
    $this->assertCount(1, $link);
    $this->assertStringContainsString(
      'List item block example',
      $link->html()
    );

    $this->assertCount(1, $crawler->filter('div#bcl-inpage-item-7'));
    $title = $crawler->filter('div#bcl-inpage-item-7 figcaption');
    $this->assertStringContainsString(
      'Quote 1',
      $title->html()
    );

    $link = $crawler->filter('a[href="#bcl-inpage-item-7"]');
    $this->assertCount(1, $link);
    $this->assertStringContainsString(
      'Quote 1',
      $link->html()
    );

    $this->assertCount(1, $crawler->filter('div#bcl-inpage-item-8'));
    $title = $crawler->filter('div#bcl-inpage-item-8 h5');
    $this->assertStringContainsString(
      'Social media block',
      $title->html()
    );

    $link = $crawler->filter('a[href="#bcl-inpage-item-8"]');
    $this->assertCount(1, $link);
    $this->assertStringContainsString(
      'Social media block',
      $link->html()
    );

    $this->assertCount(1, $crawler->filter('div#bcl-inpage-item-12'));
    $title = $crawler->filter('div#bcl-inpage-item-12 button.accordion-button');
    $this->assertStringContainsString(
      'Accordion item 1',
      $title->html()
    );

    $link = $crawler->filter('a[href="#bcl-inpage-item-12"]');
    $this->assertCount(1, $link);
    $this->assertStringContainsString(
      'Accordion item 1',
      $link->html()
    );
  }

}
