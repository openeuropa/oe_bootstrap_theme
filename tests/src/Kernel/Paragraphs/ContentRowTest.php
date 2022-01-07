<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel\Paragraphs;

use Drupal\paragraphs\Entity\Paragraph;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the "Content row" paragraph, "Inpage navigation" variant.
 */
class ContentRowTest extends ParagraphsTestBase {

  /**
   * Tests the rendering of the paragraph type.
   */
  public function testWithInpageNavigationRendering(): void {
    $paragraph_fact = [
      Paragraph::create([
        'type' => 'oe_fact',
        'field_oe_icon' => 'box-arrow-up',
        'field_oe_title' => '1529 JIRA Ticket',
        'field_oe_subtitle' => 'Jira Tickets',
        'field_oe_plain_text_long' => 'Nunc condimentum sapien ut nibh finibus suscipit vitae at justo. Morbi quis odio faucibus, commodo tortor id, elementum libero.',
      ]),
      Paragraph::create([
        'type' => 'oe_fact',
        'field_oe_icon' => 'box-arrow-up',
        'field_oe_title' => '337 Features',
        'field_oe_subtitle' => 'Feature tickets',
        'field_oe_plain_text_long' => 'Turpis varius congue venenatis, erat dui feugiat felis.',
      ]),
      Paragraph::create([
        'type' => 'oe_fact',
        'field_oe_icon' => 'box-arrow-up',
        'field_oe_title' => '107 Tests',
        'field_oe_subtitle' => 'Test tickets',
        'field_oe_plain_text_long' => 'Cras vestibulum efficitur mi, quis porta tellus rutrum ut. Quisque at pulvinar sem.',
      ]),
    ];

    $paragraph_accordion = [
      Paragraph::create([
        'type' => 'oe_accordion_item',
        'field_oe_icon' => 'box-arrow-up',
        'field_oe_text' => 'Accordion item 1',
        'field_oe_text_long' => 'Aenean at viverra tellus. Donec egestas ut ligula a condimentum. Cras sapien nulla, ornare eget lobortis vulputate, bibendum nec tellus. Fusce tristique diam quis mauris vehicula eleifend. Maecenas vitae luctus mi. Sed accumsan fermentum fermentum. Ut tristique quam at aliquam viverra. Suspendisse pulvinar risus tristique augue elementum, nec blandit sem mattis',
      ]),
      Paragraph::create([
        'type' => 'oe_accordion_item',
        'field_oe_icon' => 'box-arrow-up',
        'field_oe_text' => 'Accordion item 2',
        'field_oe_text_long' => 'Morbi pretium efficitur dolor, a vulputate sem vulputate quis. Sed dictum massa eu nulla finibus, et porta dolor efficitur. Mauris pharetra dui sed consequat faucibus. Pellentesque felis nisi, fringilla non tortor ac, laoreet feugiat ante. Curabitur vel gravida augue. Nullam erat dui, viverra a arcu non, tincidunt pulvinar tortor. Maecenas non libero consequat massa ornare posuere. Quisque ultrices ullamcorper leo, non vulputate felis vestibulum a. Nam eu tellus enim.',
      ]),
      Paragraph::create([
        'type' => 'oe_accordion_item',
        'field_oe_icon' => 'box-arrow-up',
        'field_oe_text' => 'Accordion item 3',
        'field_oe_text_long' => 'Mauris accumsan vulputate imperdiet. Aenean nec metus sem. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum maximus placerat orci, a placerat dolor iaculis vel. Nam pulvinar elementum odio ut tempor. In fermentum neque ut placerat rutrum.',
      ]),
    ];

    $sub_paragraphs = [
      Paragraph::create([
        'type' => 'oe_rich_text',
        'field_oe_title' => 'Title rich text test 1',
        'field_oe_text_long' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque ornare et elit a dictum. Maecenas lacinia eros quis eros iaculis, sit amet bibendum massa facilisis. Integer arcu nisl, fringilla nec quam vel, tincidunt maximus ex. Suspendisse ac arcu efficitur, feugiat tellus vel, viverra sapien. Etiam vitae condimentum lorem. Nulla congue ligula lacinia efficitur tempus. Duis vitae auctor enim. Nulla iaculis, diam et sagittis scelerisque, est mauris luctus sem, a imperdiet lacus diam eu dui. Morbi accumsan, augue eu gravida elementum, libero mi blandit odio, eu fringilla nunc ipsum non tellus. Suspendisse dapibus elit at lobortis pretium. Quisque vestibulum ut purus sit amet molestie. Sed eget volutpat justo, vel varius augue. Vestibulum vel risus facilisis, feugiat sem aliquam, lobortis ante.',
      ]),
      Paragraph::create([
        'type' => 'oe_links_block',
        'field_oe_text' => 'Links block test',
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
      Paragraph::create([
        'type' => 'oe_facts_figures',
        'field_oe_title' => 'Facts and Figures test',
        'field_oe_link' => [
          'uri' => 'https://www.readmore.com',
          'title' => 'Read more',
        ],
        'field_oe_paragraphs' => $paragraph_fact,
        'field_oe_list_item_block_layout' => 3,
      ]),
      Paragraph::create([
        'type' => 'oe_quote',
        'field_oe_text' => 'Quote 1',
        'field_oe_plain_text_long' => 'Maecenas id urna eleifend, elementum sapien vitae, semper massa. Curabitur mi leo, sagittis eget euismod egestas, ornare nec justo.',
      ]),
      Paragraph::create([
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
      Paragraph::create([
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
    // Check strict class row to ensure getting only the expected element.
    $this->assertCount(1, $crawler->filter('div[class="row"]'));
    $this->assertCount(8, $crawler->filter('p'));
    // Assert the left column navigation.
    $left = $crawler->filter('div.col-md-3.d-none.d-md-block');
    $nav = $left->filter('nav.position-sticky');
    $this->assertCount(1, $nav);
    $h5 = $nav->filter('h5');
    $this->assertSame('Page content', $h5->text());
    $ul = $left->filter('ul.nav.nav-pills.flex-column');
    $this->assertCount(1, $ul);
    $links = $ul->filter('li.nav-item a.nav-link');
    $this->assertCount(3, $links);
    $this->assertSame('Title rich text test 1', $links->eq(0)->text());
    $this->assertSame('Facts and Figures test', $links->eq(1)->text());
    $this->assertSame('Social media block', $links->eq(2)->text());
    // Assert the paragraphs where added into the right side column.
    $content = $crawler->filter('div.col-md-9');
    $this->assertCount(1, $content);
    $rich_text_title = $content->filter('h4.fw-bold.mb-4');
    $this->assertSame('Title rich text test 1', trim($rich_text_title->text()));
    $links_block_title = $content->filter('h2.fw-bold.pb-3.mb-3.border-bottom');
    $this->assertSame('Links block test', $links_block_title->text());
    $facts_figures = $content->filter('div.bcl-fact-figures--default h4.fw-bold');
    $this->assertStringContainsString('Facts and Figures test', $facts_figures->text());
    $blockquote_blockquote = $content->filter('blockquote.blockquote');
    $this->assertStringContainsString('Maecenas id urna eleifend', $blockquote_blockquote->text());
    $blockquote_footer = $content->filter('figcaption.blockquote-footer');
    $this->assertSame('Quote 1', trim($blockquote_footer->text()));
    $social_media_title = $content->filter('h2.fw-bold.pb-3.mb-3.border-bottom')->eq(1);
    $this->assertStringContainsString('Social media block', $social_media_title->text());
    $accordion_items = $content->filter('.accordion-item');
    $this->assertStringContainsString('Accordion item 1', $accordion_items->eq(0)->text());
    $this->assertStringContainsString('Accordion item 2', $accordion_items->eq(1)->text());
    $this->assertStringContainsString('Accordion item 3', $accordion_items->eq(2)->text());
    // Check that the wrappers where added to the correct paragraphs.
    $this->assertSame('bcl-inpage-item-1', $rich_text_title->parents()->eq(0)->attr('id'));
    $this->assertSame('bcl-inpage-item-6', $facts_figures->parents()->eq(0)->attr('id'));
    $this->assertSame('bcl-inpage-item-8', $social_media_title->parents()->eq(1)->attr('id'));
  }

}
