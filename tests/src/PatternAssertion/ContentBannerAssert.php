<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\PatternAssertions;

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
      'badges' => [
        [$this, 'assertBadgesElements'],
      ],
      'title' => [
        [$this, 'assertElementText'],
        '.card-title',
      ],
      'description' => [
        [$this, 'assertDescription'],
      ],
      'meta' => [
        [$this, 'assertMeta'],
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
