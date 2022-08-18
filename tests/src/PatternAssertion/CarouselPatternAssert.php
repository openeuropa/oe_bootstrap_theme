<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\PatternAssertion;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the carousel pattern.
 */
class CarouselPatternAssert extends BasePatternAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions(string $variant): array {
    return [
      'items' => [
        [$this, 'assertItems'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function assertBaseElements(string $html, string $variant): void {}

  /**
   * Asserts the carousel pattern items.
   *
   * @param array[] $expected
   *   The expected item values.
   *   Each item is an array, with the following keys:
   *     - 'image': The link destination.
   *     - 'title': The label, if exists.
   *     - 'caption': The name of the icon.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertItems(array $expected, Crawler $crawler): void {
    echo $crawler->html();
    $items = $crawler->filter('div.ecl-carousel__container div.ecl-carousel__slides div.ecl-carousel__slide');
    self::assertCount(count($expected_items), $items);
    foreach ($expected_items as $index => $expected_item) {
      $item = $items->eq($index);
    }
  }

}
