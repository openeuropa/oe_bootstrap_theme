<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel\Paragraphs;

use Drupal\filter\Entity\FilterFormat;
use Drupal\paragraphs\Entity\Paragraph;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the rendering for timeline paragraph type.
 */
class TimelineParagraphTest extends ParagraphsTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'oe_paragraphs_timeline',
    'oe_content_timeline_field',
    'node',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installConfig([
      'oe_paragraphs_timeline',
      'oe_content_timeline_field',
      'node',
    ]);

    // Remove the auto-paragraph and url-to-link filters from the plain text.
    /** @var \Drupal\filter\Entity\FilterFormat $format */
    $format = FilterFormat::load('plain_text');
    $format->filters();
    $format->removeFilter('filter_url');
    $format->removeFilter('filter_autop');
    $format->save();

    FilterFormat::create([
      'format' => 'filtered_html',
      'name' => 'Filtered HTML',
      'filters' => [
        'filter_html' => [
          'status' => 1,
          'settings' => [
            'allowed_html' => '<strong>',
          ],
        ],
      ],
    ])->save();

    FilterFormat::create([
      'format' => 'full_html',
      'name' => 'Full HTML',
    ])->save();

    $this->container->get('theme_installer')->install(['oe_bootstrap_theme']);
    $this->config('system.theme')->set('default', 'oe_bootstrap_theme')->save();
    $this->container->set('theme.registry', NULL);
  }

  /**
   * Test 'timeline' paragraph rendering.
   */
  public function testTimeline(): void {
    $paragraph = Paragraph::create([
      'type' => 'oe_timeline',
      'field_oe_timeline_expand' => '3',
      'field_oe_timeline' => [
        [
          'label' => 'Label 1',
          'title' => 'Title 1',
          'body' => 'Description 1',
        ],
        [
          'label' => 'Label 2',
          'title' => 'Title 2',
          'body' => '<p>Description 2</p>',
        ],
        [
          'label' => 'Label 3',
          'title' => 'Title 3',
          'body' => '<p>Description <strong>3</strong></p>',
          'format' => 'plain_text',
        ],
        [
          'label' => 'Label 4',
          'title' => 'Title 4',
          'body' => '<p>Description <strong>4</strong></p>',
          'format' => 'filtered_html',
        ],
        [
          'label' => 'Label 5',
          'title' => 'Title 5',
          'body' => '<p>Description <strong>5</strong></p>',
          'format' => 'full_html',
        ],
        [
          'label' => 'Label 6',
          'title' => 'Title 6',
          'body' => 'Description 6',
        ],
      ],
    ]);

    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    // No heading should be rendered if the paragraph has no heading set.
    $this->assertCount(0, $crawler->filter('h4'));
    $this->assertCount(1, $crawler->filter('ol.bcl-timeline'));
    $this->assertCount(7, $crawler->filter('ol.bcl-timeline li'));
    $this->assertCount(1, $crawler->filter('ol.bcl-timeline li .label-collapsed'));
    $this->assertCount(1, $crawler->filter('ol.bcl-timeline li.bcl-timeline__item--toggle'));
    $this->assertEquals('Label 1', trim($crawler->filter('ol.bcl-timeline li:nth-child(1) h5')->html()));
    $this->assertEquals('Title 1', trim($crawler->filter('ol.bcl-timeline li:nth-child(1) h6')->html()));
    $this->assertEquals('Description 1', trim($crawler->filter('ol.bcl-timeline li:nth-child(1) div')->html()));
    $this->assertEquals('Label 2', trim($crawler->filter('ol.bcl-timeline li:nth-child(2) h5')->html()));
    $this->assertEquals('Title 2', trim($crawler->filter('ol.bcl-timeline li:nth-child(2) h6')->html()));
    // Explicit format "plain_text" specified.
    $this->assertEquals('&lt;p&gt;Description 2&lt;/p&gt;', trim($crawler->filter('ol.bcl-timeline li:nth-child(2) div')->html()));
    $this->assertEquals('Label 3', trim($crawler->filter('ol.bcl-timeline li:nth-child(3) h5')->html()));
    $this->assertEquals('Title 3', trim($crawler->filter('ol.bcl-timeline li:nth-child(3) h6')->html()));
    $this->assertEquals('&lt;p&gt;Description &lt;strong&gt;3&lt;/strong&gt;&lt;/p&gt;', trim($crawler->filter('ol.bcl-timeline li:nth-child(3) div')->html()));
    $this->assertEquals('Label 4', trim($crawler->filter('ol.bcl-timeline li:nth-child(4) h5')->html()));
    $this->assertEquals('Title 4', trim($crawler->filter('ol.bcl-timeline li:nth-child(4) h6')->html()));
    // Explicit format "filtered_html" specified.
    $this->assertEquals('Description <strong>4</strong>', trim($crawler->filter('ol.bcl-timeline li:nth-child(4) div')->html()));
    $this->assertEquals('<p>Description <strong>5</strong></p>', trim($crawler->filter('ol.bcl-timeline li:nth-child(5) div')->html()));
    $this->assertEquals('Label 5', trim($crawler->filter('ol.bcl-timeline li:nth-child(5) h5')->html()));
    $this->assertEquals('Title 5', trim($crawler->filter('ol.bcl-timeline li:nth-child(5) h6')->html()));
    $this->assertEquals('Label 6', trim($crawler->filter('ol.bcl-timeline li:nth-child(6) h5')->html()));
    $this->assertEquals('Title 6', trim($crawler->filter('ol.bcl-timeline li:nth-child(6) h6')->html()));
    // Explicit format "full_html" specified.
    $this->assertEquals('Description 6', trim($crawler->filter('ol.bcl-timeline li:nth-child(6) div')->html()));
    $this->assertEquals('Show more 3 items', trim($crawler->filter('button .label-collapsed')->text()));

    // Increase limit to print all the items and set timeline heading.
    $paragraph->set('field_oe_timeline_expand', '7');
    $paragraph->set('field_oe_title', 'Timeline Heading');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    // Assert rendering is updated.
    $this->assertCount(1, $crawler->filter('h4.fw-bold.mb-4'));
    $this->assertEquals('Timeline Heading', trim($crawler->filter('h4.fw-bold.mb-4')->text()));
    $this->assertCount(6, $crawler->filter('ol.bcl-timeline li'));
    $this->assertCount(0, $crawler->filter('ol.bcl-timeline label-collapsed'));
    $this->assertCount(0, $crawler->filter('ol.bcl-timeline li.bcl-timeline__item--toggle'));
  }

}
