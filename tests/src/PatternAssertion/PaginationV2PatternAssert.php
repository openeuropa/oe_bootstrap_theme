<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\PatternAssertion;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the pagination v2 pattern.
 */
class PaginationV2PatternAssert extends BasePatternAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions(string $variant): array {
    return [
      'items' => [
        [$this, 'assertItems'],
      ],
      'alignment' => [
        [$this, 'assertAlignment'],
      ],
      'size' => [
        [$this, 'assertSize'],
      ],
      'attributes' => [
        [$this, 'assertElementAttributes'],
        'nav.pager.pagination-v2',
      ],
      'list_attributes' => [
        [$this, 'assertElementAttributes'],
        'nav.pager.pagination-v2 > ul.pagination',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function assertBaseElements(string $html, string $variant): void {
    $this->assertCounts([
      'nav.pager.pagination-v2' => 1,
      'nav.pager.pagination-v2 > ul.pagination' => 1,
    ], new Crawler($html));
  }

  /**
   * Asserts the pager items.
   *
   * @param array[] $expected
   *   Expected pager items.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertItems(array $expected, Crawler $crawler): void {
    $expected_count = count($expected);
    $this->assertCounts([
      'nav.pager.pagination-v2 > ul.pagination > li.page-item' => $expected_count,
      'nav.pager.pagination-v2 > ul.pagination > li.page-item > .page-link:first-child' => $expected_count,
    ], $crawler);

    $items = $crawler->filter('nav.pager.pagination-v2 > ul.pagination > li.page-item');
    $icon_pattern_assert = new IconPatternAssert();

    foreach ($expected as $index => $expected_item) {
      self::assertGreaterThan($index, $items->count());
      $item = $items->eq($index);

      $link = $item->filter('.page-link');
      $this->assertCount(1, $link);

      if (isset($expected_item['item_classes'])) {
        $classes = is_array($expected_item['item_classes']) ? $expected_item['item_classes'] : [$expected_item['item_classes']];
        foreach ($classes as $class) {
          self::assertStringContainsString($class, $item->attr('class') ?? '');
        }
      }

      if (isset($expected_item['url'])) {
        self::assertSame('a', $link->nodeName());
        self::assertSame($expected_item['url'], $link->attr('href'));
      }
      else {
        self::assertSame('div', $link->nodeName());
        self::assertNull($link->attr('href'));
      }

      if (isset($expected_item['icon'])) {
        $icon_pattern_assert->assertPattern([
          'name' => $expected_item['icon'],
        ], $link->html());
      }

      if (isset($expected_item['desktop_label'])) {
        self::assertSame($expected_item['desktop_label'], trim($link->filter('.d-none.d-md-inline-block')->text()));
      }

      if (isset($expected_item['mobile_label'])) {
        self::assertSame($expected_item['mobile_label'], trim($link->filter('.d-inline-block.d-md-none')->text()));
      }

      if (isset($expected_item['label']) && !isset($expected_item['desktop_label']) && !isset($expected_item['mobile_label'])) {
        self::assertSame($expected_item['label'], trim($link->text()));
      }

      if (array_key_exists('aria_label', $expected_item)) {
        self::assertSame($expected_item['aria_label'], $link->attr('aria-label'));
      }
      else {
        self::assertNotEmpty($link->attr('aria-label'));
      }

      if (!empty($expected_item['active'])) {
        self::assertStringContainsString('active', $item->attr('class') ?? '');
        self::assertSame('page', $link->attr('aria-current'));
        self::assertSame('0', $link->attr('tabindex'));
      }

      if (!empty($expected_item['disabled'])) {
        self::assertStringContainsString('disabled', $item->attr('class') ?? '');
        self::assertSame('true', $link->attr('aria-disabled'));
      }
    }
  }

  /**
   * Asserts the alignment.
   *
   * @param string $expected
   *   The expected alignment.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertAlignment(string $expected, Crawler $crawler): void {
    $this->assertElementExists('nav.pager.pagination-v2 > ul.justify-content-md-' . $expected, $crawler);
  }

  /**
   * Asserts the size.
   *
   * @param string $expected
   *   The expected size.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertSize(string $expected, Crawler $crawler): void {
    $this->assertElementExists('nav.pager.pagination-v2 > ul.pagination-' . $expected, $crawler);
  }

}
