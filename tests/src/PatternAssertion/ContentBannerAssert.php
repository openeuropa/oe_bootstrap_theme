<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\PatternAssertion;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the page header pattern.
 */
class ContentBannerAssert extends BasePatternAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions(string $variant): array {
    return [
      'image' => [
        [$this, 'assertImage'],
        '.card-img-top',
      ],
      'title' => [
        [$this, 'assertElementText'],
        '.card-title.bcl-heading',
      ],
      'title_tag' => [
        [$this, 'assertTitleTag'],
      ],
      'background' => [
        [$this, 'assertBackground'],
      ],
      'badges' => [
        [$this, 'assertBadgesElements'],
      ],
      'links' => [
        [$this, 'assertLinks'],
      ],
      'content' => [
        [$this, 'assertElementText'],
        '.banner-content',
      ],
      'meta' => [
        [$this, 'assertMeta'],
      ],
      'action_bar' => [
        [$this, 'assertActionBar'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function assertBaseElements(string $html, string $variant): void {
    $crawler = new Crawler($html);
    $page_header = $crawler->filter('.bcl-content-banner');
    self::assertCount(1, $page_header);
  }

  /**
   * Asserts the rendered html of the action-bar.
   *
   * @param string|null $expected
   *   The expected value.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertActionBar($expected, Crawler $crawler): void {
    if (is_null($expected)) {
      $this->assertElementNotExists('.bcl-content-banner > .container > *:last-child', $crawler);
      return;
    }
    $this->assertElementExists('.bcl-content-banner > .container > *:last-child', $crawler);
    $element = $crawler->filter('.bcl-content-banner > .container > *:last-child');
    self::assertEquals($expected, $element->outerHtml());
  }

  /**
   * Asserts the background class of the content banner.
   *
   * @param string $expected
   *   The expected background class.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The dom crawler.
   */
  protected function assertBackground(string $expected, Crawler $crawler): void {
    $element = $crawler->filter('.bcl-content-banner');

    if ($expected === 'white') {
      self::assertStringContainsString('bg-white', $element->attr('class'));
    }
    elseif ($expected === 'gray') {
      self::assertStringContainsString('bg-lighter', $element->attr('class'));
    }
    else {
      self::assertStringContainsString('bg-transparent', $element->attr('class'));
    }
  }

  /**
   * Checks the tag used for the title.
   *
   * @param string $expected
   *   The expected tag.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertTitleTag(string $expected, Crawler $crawler): void {
    $this->assertElementExists($expected . '.card-title.bcl-heading', $crawler);
  }

  /**
   * Asserts the content banner description.
   *
   * @param string $text
   *   The expected description.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The dom crawler.
   */
  protected function assertDescription(string $text, Crawler $crawler): void {
    $element = $crawler->filter('.card-body');
    self::assertCount(1, $element);
    self::assertStringContainsString($text, $element->text());
  }

  /**
   * Asserts the content banner links.
   *
   * @param array $expected
   *   The expected links.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The dom crawler.
   */
  protected function assertLinks(array $expected, Crawler $crawler): void {
    $actualLinks = $crawler->filter('.card-body div:last-child > div')->each(function (Crawler $element) {
      return [
        'label' => $element->filter('a')->text(),
        'path' => $element->filter('a')->attr('href'),
      ];
    });

    // Assert that the actual links match the expected links.
    $this->assertEquals($expected, $actualLinks);
  }

  /**
   * Asserts the content banner meta.
   *
   * @param array $expected
   *   The expected meta.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The dom crawler.
   */
  protected function assertMeta(array $expected, Crawler $crawler): void {
    $actual = $crawler->filter('.card-body span.text-muted.me-3')->each(function (Crawler $element) {
      return trim($element->text());
    });

    $this->assertEquals($expected, $actual);
  }

}
