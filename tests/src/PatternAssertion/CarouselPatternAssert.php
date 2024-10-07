<?php

declare(strict_types=1);

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
      'title' => [
        [$this, 'assertElementText'],
        '.bcl-heading',
      ],
      'title_tag' => [
        [$this, 'assertElementTag'],
        '.bcl-heading',
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

    $expected_previous = $expected['show_controls'] ? 'Previous' : NULL;
    $expected_next = $expected['show_controls'] ? 'Next' : NULL;
    $this->assertElementText($expected_previous, '.carousel-control-prev', $crawler);
    $this->assertElementText($expected_next, '.carousel-control-next', $crawler);

    if (!empty($expected['show_indicators'])) {
      $this->assertElementExists('.carousel .carousel-indicators button', $crawler);
    }
    else {
      $this->assertElementNotExists('.carousel .carousel-indicators', $crawler);
    }

    self::assertArrayNotHasKey('autoinit', $expected, 'The autoinit setting is deprecated and it has been replaced with autoplay.');

    // This attribute maps to the deprecated usage of the autoplay attribute.
    // It should be never present.
    $this->assertElementAttribute(NULL, '.carousel', 'data-bs-interval', $crawler);

    $expected_autoplay = !empty($expected['autoplay']) ? 'carousel' : NULL;
    $this->assertElementAttribute($expected_autoplay, '.carousel', 'data-bs-ride', $crawler);

    $expected_disable_touch = !empty($expected['disable_touch']) ? 'false' : NULL;
    $this->assertElementAttribute($expected_disable_touch, '.carousel', 'data-bs-touch', $crawler);
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

      // Check if the HTML content contains video or iframe.
      $isPlayable = strpos($item->html(), '<video') !== FALSE || strpos($item->html(), '<iframe') !== FALSE;

      try {
        self::assertStringContainsString($expected_item['image'], $item->html());
        $this->assertElementAttribute($expected_item['interval'] ?? 0, '.carousel-item', 'data-bs-interval', $item);
        if ($isPlayable) {
          $this->assertElementNotExists('.carousel-caption', $item);
          continue;
        }
        $this->assertElementText($expected_item['caption_title'] ?? NULL, '.carousel-caption .fs-5', $item);
        if (isset($expected_item['caption'])) {
          $this->assertElementTextContains($expected_item['caption'], '.carousel-caption', $item);
        }
        if (isset($expected_item['caption_classes'])) {
          $this->assertElementExists('.carousel-caption > .' . $expected_item['caption_classes'], $item);
        }
        if (isset($expected_item['link'])) {
          $this->assertElementText($expected_item['link']['label'], '.carousel-caption a', $item);
          $this->assertElementAttribute($expected_item['link']['path'], '.carousel-caption a', 'href', $item);
          $this->assertElementExists('.carousel-caption a svg', $item);
        }
        else {
          $this->assertElementNotExists('.carousel-caption a', $item);
        }
      }
      catch (\Exception $e) {
        throw new \Exception(sprintf('Failed asserting data for item %s.', $index), 0, $e);
      }
    }
  }

}
