<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\Kernel\Paragraphs;

use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Tests\oe_bootstrap_theme\Kernel\Paragraphs\ParagraphsTestBase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the "Links block" and "Social media follow" paragraph.
 */
class LinksBlockTest extends ParagraphsTestBase {

  /**
   * Tests the rendering of the paragraph type.
   */
  public function testRendering(): void {
    // Create social media follow paragraph with horizontal variant.
    $paragraph = Paragraph::create([
      'type' => 'oe_social_media_follow',
      'field_oe_title' => 'Social media title',
      'field_oe_social_media_variant' => 'horizontal',
      'field_oe_social_media_links' => $this->getSocialMediaLinks(),
      'field_oe_social_media_see_more' => [
        'title' => 'Other social networks',
        'uri' => 'https://europa.eu/european-union/contact/social-networks_en',
      ],
    ]);
    $paragraph->save();

    // Testing: SocialMediaFollow horizontal gray.
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('div.bg-light.px-4.py-3'));
    $this->assertCount(1, $crawler->filter('h5.fw-bold.pb-3.mb-3.border-bottom'));
    $this->assertCount(1, $crawler->filter('ul.ps-0.mb-0'));
    $this->assertCount(15, $crawler->filter('li.list-unstyled'));
    $this->assertCount(14, $crawler->filter('li.list-unstyled.me-4-5'));
    $this->assertCount(15, $crawler->filter('li.d-inline'));

    // Verify that links are rendered.
    $links = $crawler->filter('ul');

    // Verify that the paragraph contains all the links.
    $links_html = $links->html();
    $this->assertStringContainsString('Email', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 5) = \'#email\']'));
    $this->assertStringContainsString('Facebook', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 8) = \'#facebook\']'));
    $this->assertStringContainsString('Flickr', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 6) = \'#flickr\']'));
    $this->assertStringContainsString('Google+', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 6) = \'#google\']'));
    $this->assertStringContainsString('Instagram', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 9) = \'#instagram\']'));
    $this->assertStringContainsString('LinkedIn', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 8) = \'#linkedin\']'));
    $this->assertStringContainsString('Pinterest', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 9) = \'#pinterest\']'));
    $this->assertStringContainsString('RSS', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 3) = \'#rss\']'));
    $this->assertStringContainsString('Storify', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 7) = \'#storify\']'));
    $this->assertStringContainsString('1st Twitter', $links_html);
    $this->assertStringContainsString('2nd Twitter', $links_html);
    $this->assertCount(2, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 7) = \'#twitter\']'));
    $this->assertStringContainsString('Yammer', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 6) = \'#yammer\']'));
    $this->assertStringContainsString('Youtube', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 7) = \'#youtube\']'));
    $this->assertStringContainsString('Other social networks', $links_html);

    // Testing: SocialMediaFollow vertical gray.
    $paragraph->get('field_oe_social_media_variant')->setValue('vertical');
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('div.bg-light.px-4.py-3'));
    $this->assertCount(1, $crawler->filter('h5.fw-bold.pb-3.mb-3.border-bottom'));
    $this->assertCount(1, $crawler->filter('ul.ps-0.mb-0'));
    $this->assertCount(15, $crawler->filter('li.list-unstyled'));
    $this->assertCount(14, $crawler->filter('li.list-unstyled.me-4-5'));
    $this->assertCount(0, $crawler->filter('li.d-inline'));

    // Verify that links are rendered.
    $links = $crawler->filter('ul');

    // Verify that the paragraph contains all the links.
    $links_html = $links->html();
    $this->assertStringContainsString('Email', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 5) = \'#email\']'));
    $this->assertStringContainsString('Facebook', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 8) = \'#facebook\']'));
    $this->assertStringContainsString('Flickr', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 6) = \'#flickr\']'));
    $this->assertStringContainsString('Google+', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 6) = \'#google\']'));
    $this->assertStringContainsString('Instagram', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 9) = \'#instagram\']'));
    $this->assertStringContainsString('LinkedIn', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 8) = \'#linkedin\']'));
    $this->assertStringContainsString('Pinterest', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 9) = \'#pinterest\']'));
    $this->assertStringContainsString('RSS', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 3) = \'#rss\']'));
    $this->assertStringContainsString('Storify', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 7) = \'#storify\']'));
    $this->assertStringContainsString('1st Twitter', $links_html);
    $this->assertStringContainsString('2nd Twitter', $links_html);
    $this->assertCount(2, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 7) = \'#twitter\']'));
    $this->assertStringContainsString('Yammer', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 6) = \'#yammer\']'));
    $this->assertStringContainsString('Youtube', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 7) = \'#youtube\']'));
    $this->assertStringContainsString('Other social networks', $links_html);

    // Create Links Block paragraph.
    $paragraph = Paragraph::create([
      'type' => 'oe_links_block',
      'field_oe_text' => 'More information',
      'field_oe_links' => $this->getBlockLinks(),
    ]);
    $paragraph->save();

    // Testing: LinksBlock vertical gray.
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('div.bg-light.px-4.py-3'));
    $this->assertCount(1, $crawler->filter('h5.fw-bold.pb-3.mb-3.border-bottom'));
    $this->assertCount(1, $crawler->filter('ul.ps-0.mb-0'));
    $this->assertCount(3, $crawler->filter('li.list-unstyled'));
    $this->assertCount(2, $crawler->filter('li.list-unstyled.me-4-5'));
    $this->assertCount(0, $crawler->filter('li.d-inline'));
  }

  /**
   * Returns a list of link items for Social media follow paragraph.
   *
   * @return array
   *   An array of link items.
   */
  protected function getSocialMediaLinks(): array {
    return [
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
      [
        'title' => 'Flickr',
        'uri' => 'https://www.flickr.com',
        'link_type' => 'flickr',
      ],
      [
        'title' => 'Google+',
        'uri' => 'https://google.com',
        'link_type' => 'google',
      ],
      [
        'title' => 'Instagram',
        'uri' => 'https://instagram.com',
        'link_type' => 'instagram',
      ],
      [
        'title' => 'LinkedIn',
        'uri' => 'https://linkedin.com',
        'link_type' => 'linkedin',
      ],
      [
        'title' => 'Pinterest',
        'uri' => 'https://pinterest.com',
        'link_type' => 'pinterest',
      ],
      [
        'title' => 'RSS',
        'uri' => 'https://rss-example.com',
        'link_type' => 'rss',
      ],
      [
        'title' => 'Storify',
        'uri' => 'https://storify.com',
        'link_type' => 'storify',
      ],
      [
        'title' => '1st Twitter',
        'uri' => 'https://twitter.com',
        'link_type' => 'twitter',
      ],
      [
        'title' => '2nd Twitter',
        'uri' => 'https://twitter.com',
        'link_type' => 'twitter',
      ],
      [
        'title' => 'Yammer',
        'uri' => 'https://yammer.com',
        'link_type' => 'yammer',
      ],
      [
        'title' => 'Youtube',
        'uri' => 'https://youtube.com',
        'link_type' => 'youtube',
      ],
      [
        'title' => 'Vimeo',
        'uri' => 'https://vimeo.com',
        'link_type' => 'vimeo',
      ],
    ];
  }

  /**
   * Returns a list of link items for Block Links paragraph.
   *
   * @return array
   *   An array of link items.
   */
  protected function getBlockLinks(): array {
    return [
      [
        'title' => 'European Commission',
        'uri' => 'https://example.com',
      ],
      [
        'title' => 'Priorities',
        'uri' => 'https://example.com',
      ],
      [
        'title' => 'Jobs, Growth and Investment',
        'uri' => 'https://example.com',
      ],
    ];
  }

}
