<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel\PatternAssertions;

use Drupal\Tests\oe_bootstrap_theme\Kernel\Paragraphs\ParagraphsTestBase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class for asserting links block paragraphs.
 */
class LinksBlockAssertion extends ParagraphsTestBase {

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

  /**
   * Assert links from social media.
   *
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertSocialLinks(Crawler $crawler): void {
    $links = $crawler->filter('ul');
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
  }

  /**
   * Assert Background is gray.
   *
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertBackgroundGray(Crawler $crawler): void {
    $this->assertCount(1, $crawler->filter('div.bg-light.px-4.py-3'));
  }

  /**
   * Assert Background is transparent.
   *
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertBackgroundTransparent(Crawler $crawler): void {
    $this->assertCount(0, $crawler->filter('div.bg-light.px-4.py-3'));
  }

  /**
   * Assert Links Block with Social Media is rendering correctly.
   *
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertSocialMediaLinksBlockRendering(Crawler $crawler): void {
    $this->assertCount(1, $crawler->filter('h5.fw-bold.pb-3.mb-3.border-bottom'));
    $this->assertCount(1, $crawler->filter('ul.ps-0.mb-0'));
    $this->assertCount(15, $crawler->filter('li.list-unstyled'));
    $this->assertCount(14, $crawler->filter('li.list-unstyled.me-4-5'));
  }

  /**
   * Assert direction is horizontal.
   *
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   * @param bool $social
   *   Flag to determine if is block links with social media or not.
   */
  protected function assertHorizontalLinks(Crawler $crawler, $social = TRUE): void {
    $this->assertCount($social ? 15 : 3, $crawler->filter('li.d-inline'));
  }

  /**
   * Assert direction is vertical.
   *
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertVerticalLinks(Crawler $crawler): void {
    $this->assertCount(0, $crawler->filter('li.d-inline'));
  }

  /**
   * Assert Links Block is rendering correctly.
   *
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertLinksBlockRendering(Crawler $crawler): void {
    $this->assertCount(1, $crawler->filter('h5.fw-bold.pb-3.mb-3.border-bottom'));
    $this->assertCount(1, $crawler->filter('ul.ps-0.mb-0'));
    $this->assertCount(3, $crawler->filter('li.list-unstyled'));
    $this->assertCount(2, $crawler->filter('li.list-unstyled.me-4-5'));
  }

}
