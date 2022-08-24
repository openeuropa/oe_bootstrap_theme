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
      'settings' => [
        [$this, 'assertSettings'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function assertBaseElements(string $html, string $variant): void {
    $crawler = new Crawler($html);

    $this->assertElementExists('.carousel .carousel-inner', $crawler);
  }

  /**
   * Asserts the carousel pattern settings.
   *
   * @param array[] $expected
   *   The expected settings.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertSettings(array $expected, Crawler $crawler): void {
    if (!empty($expected['fade'])) {
      $this->assertElementExists('.carousel-fade', $crawler);
    }
    else {
      $this->assertElementNotExists('.carousel-fade', $crawler);
    }

    if (!empty($expected['show_controls'])) {
      $this->assertElementText('Previous', '.carousel-control-prev', $crawler);
      $this->assertElementText('Next', '.carousel-control-next', $crawler);
    }
    else {
      $this->assertElementNotExists('.carousel-control-prev', $crawler);
      $this->assertElementNotExists('.carousel-control-next', $crawler);
    }

    if (!empty($expected['show_indicators'])) {
      $this->assertElementExists('.carousel .carousel-indicators button', $crawler);
    }
    else {
      $this->assertElementNotExists('.carousel .carousel-indicators', $crawler);
    }

    if (!empty($expected['autoplay'])) {
      $this->assertElementAttribute(NULL, '.carousel', 'data-bs-interval', $crawler);
    }
    else {
      $this->assertElementAttribute('false', '.carousel', 'data-bs-interval', $crawler);
    }

    if (!empty($expected['autoinit'])) {
      $this->assertElementAttribute('carousel', '.carousel', 'data-bs-ride', $crawler);
    }
    else {
      $this->assertElementAttribute(NULL, '.carousel', 'data-bs-ride', $crawler);
    }

    if (!empty($expected['disable_touch'])) {
      $this->assertElementAttribute('false', '.carousel', 'data-bs-touch', $crawler);
    }
    else {
      $this->assertElementAttribute(NULL, '.carousel', 'data-bs-touch', $crawler);
    }
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
