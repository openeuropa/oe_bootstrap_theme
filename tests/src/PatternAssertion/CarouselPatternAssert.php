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
  protected function assertBaseElements(string $html, string $variant): void {
    $crawler = new Crawler($html);
    $this->assertElementExists('.carousel .carousel-indicators', $crawler);
    $this->assertElementExists('.carousel .carousel-inner', $crawler);
    self::assertCount(4, $crawler->filter('.carousel button'));
    $this->assertElementText('Previous', '.carousel-control-prev', $crawler);
    $this->assertElementText('Next', '.carousel-control-next', $crawler);
    self::assertCount(2, $crawler->filter('.carousel-indicators button'));
  }

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
