<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\PatternAssertion;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the featured media pattern.
 */
class FeaturedMediaPatternAssert extends BasePatternAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions(string $variant): array {
    return [
      'with_text' => [
        [$this, 'assertWithText'],
      ],
      'with_text_only' => [
        [$this, 'assertWithTextOnly'],
      ],
      'text_position' => [
        [$this, 'assertTextPosition'],
      ],
      'image' => [
        [$this, 'assertImage'],
        'figure > img',
      ],
      'iframe' => [
        [$this, 'assertIframe'],
        'figure > .ratio > iframe',
      ],
      'caption' => [
        [$this, 'assertElementText'],
        'figure > figcaption',
      ],
      'title' => [
        [$this, 'assertElementText'],
        'h2',
      ],
      'subtitle' => [
        [$this, 'assertElementText'],
        'h5',
      ],
      'text' => [
        [$this, 'assertText'],
      ],
      'link' => [
        [$this, 'assertLink'],
      ],
      'ratio' => [
        [$this, 'assertRatio'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function assertBaseElements(string $html, string $variant): void {
    $crawler = new Crawler($html);

    $this->assertElementExists('div.bcl-featured-item', $crawler);
  }

  /**
   * Asserts if the featured media has text or not.
   *
   * @param bool $expected
   *   The expected state.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertWithText(bool $expected, Crawler $crawler): void {
    if ($expected) {
      $this->assertElementExists('div.bcl-featured-item > div.row', $crawler);
      // Assert that two columns are present in the html.
      $this->assertCount(2, $crawler->filter('div.bcl-featured-item > div.row > div.col-12.col-md-6'));
      return;
    }
    $this->assertElementNotExists('div.col-12.col-md-6', $crawler);
  }

  /**
   * Asserts if the featured media has only text or not.
   *
   * @param bool $expected
   *   The expected state.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertWithTextOnly(bool $expected, Crawler $crawler): void {
    if ($expected) {
      $this->assertWithText(TRUE, $crawler);
      $this->assertElementNotExists('figure img', $crawler);
      $this->assertElementNotExists('figure iframe', $crawler);
      return;
    }
    $this->assertElementExists('figure > img, figure iframe', $crawler);
  }

  /**
   * Asserts featured media text position.
   *
   * @param string $expected
   *   The expected position. Possible values are: 'left' or 'right'.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertTextPosition(string $expected, Crawler $crawler): void {
    self::assertContains($expected, ['left', 'right'], 'Invalid text position.');
    $this->assertWithText(TRUE, $crawler);
    $order = $expected === 'left' ? 2 : 1;
    $this->assertElementExists("div.row > div.col-12.col-md-6.order-{$order} > figure", $crawler);
  }

  /**
   * Asserts featured media text string.
   *
   * @param string $expected
   *   The expected text string.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertText(string $expected, Crawler $crawler): void {
    $this->assertWithText(TRUE, $crawler);
    $order = $crawler->filter('div.row > div.col-12.col-md-6.order-1 > figure')->count() > 0 ? 2 : 1;
    // As the text container may also include an additional link, we have to
    // use 'contains' instead of checking for equality.
    $this->assertElementTextContains($expected, "div.row > div.col-12.col-md-6.order-{$order}", $crawler);
  }

  /**
   * Asserts featured media link.
   *
   * @param array $expected
   *   The expected link array. Required keys are: 'href' and 'label'.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertLink(array $expected, Crawler $crawler): void {
    self::assertArrayHasKey('href', $expected, 'The link array requires `href` key');
    self::assertArrayHasKey('label', $expected, 'The link array requires `label` key');
    $order = $crawler->filter('div.row > div.col-12.col-md-6.order-1 > figure')->count() > 0 ? 2 : 1;
    $selector = "div.row > div.col-12.col-md-6.order-{$order} > a";
    $this->assertElementExists($selector, $crawler);
    $element = $crawler->filter($selector);
    self::assertEquals($expected['href'], $element->attr('href'));
    $this->assertElementText($expected['label'], $selector, $crawler);
  }

  /**
   * Asserts featured media ratio.
   *
   * @param string $expected
   *   The expected ratio.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertRatio(string $expected, Crawler $crawler): void {
    $order = $crawler->filter('div.row > div.col-12.col-md-6.order-1 > figure')->count() > 0 ? 1 : 2;
    $this->assertElementExists("div.row > div.col-12.col-md-6.order-{$order} > figure > .ratio.ratio-{$expected}", $crawler);
  }

}
