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
      'image_size' => [
        [$this, 'assertImageSize'],
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
    $content_banner = $crawler->filter('.bcl-content-banner');
    self::assertCount(1, $content_banner);
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

    self::assertStringContainsString('bg-' . $expected, $element->attr('class'));
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
   * Checks the image size used for the image.
   *
   * @param string $expected
   *   The expected tag.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertImageSize(string $expected, Crawler $crawler): void {
    $element = $crawler->filter('.bcl-card-start-col');

    $expectedClass = match ($expected) {
      'xl' => 'bcl-size-extra-large',
      'lg' => 'bcl-size-large',
      default => 'bcl-card-start-col',
    };

    if ($expectedClass === 'bcl-card-start-col') {
      $classes = explode(' ', $element->attr('class'));
      self::assertCount(1, $classes);
    }

    self::assertStringContainsString($expectedClass, $element->attr('class'));
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
