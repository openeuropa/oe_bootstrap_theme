<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\PatternAssertion;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the columns pattern.
 */
class ColumnsPatternAssert extends BasePatternAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions(string $variant): array {
    return [
      'columns' => [
        [$this, 'assertColumns'],
      ],
      'tag' => [
        [$this, 'assertElementTag'],
        '.columns',
      ],
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

    // Only one wrapper should be rendered, as first element.
    $this->assertElementExists('body > .columns', $crawler);
    $this->assertElementExists('.columns', $crawler);
  }

  /**
   * Asserts that the pattern is using a certain amount of columns.
   *
   * @param int $columns
   *   The columns.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertColumns(int $columns, Crawler $crawler): void {
    $wrapper = $crawler->filter('body > .columns');
    self::assertEquals(
      sprintf('--columns-grid--column-count: %s;', $columns),
      $wrapper->attr('style')
    );
  }

  /**
   * Asserts the items markup.
   *
   * @param array $expected
   *   The expected item markup.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertItems(array $expected, Crawler $crawler): void {
    $elements = $crawler->filter('body > .columns > .columns__item');
    self::assertEquals($expected, $elements->each(fn($element) => $element->html()));
  }

}
