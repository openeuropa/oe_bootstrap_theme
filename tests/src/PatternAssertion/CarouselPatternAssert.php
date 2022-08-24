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
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertItems(array $expected, Crawler $crawler): void {
    $items = $crawler->filter('.carousel .carousel-inner .carousel-item');
    self::assertSameSize($expected, $items);

    foreach ($expected as $index => $expected_item) {
      $item = $items->eq($index);

      // Assert the image.
      $this->assertImage($expected_item['image'], 'img', $item);

      // Assert the title.
      if (!isset($expected_item['title'])) {
        $this->assertElementNotExists('.carousel-caption h5', $item);
      }
      else {
        $this->assertElementText($expected_item['title'], '.carousel-caption h5', $item);
      }

      // Assert the caption.
      if (isset($expected_item['caption'])) {
        $this->assertElementTextContains($expected_item['caption'], '.carousel-caption', $item);
      }
    }
  }

}
