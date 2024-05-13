<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\PatternAssertions;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for in-page-navigation.
 */
class InPageNavigationAssert extends BasePatternAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions(string $variant): array {
    return [
      'title' => [
        [$this, 'assertElementText'],
        'h2',
      ],
      'links' => [
        [$this, 'assertList'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function assertBaseElements(string $html, string $variant): void {
    $crawler = new Crawler($html);
    $page_header = $crawler->filter('body');
    self::assertCount(1, $page_header);
  }

  /**
   * Asserts the in-page-navigation links list.
   *
   * @param array|null $expected
   *   The expected description values.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertList($expected, Crawler $crawler): void {
    $this->assertElementExists('ul.nav-pills', $crawler);

    $actual = [];
    $crawler->filter('ul.nav-pills  a.nav-link')->each(function (Crawler $node) use (&$actual) {
      $actual[] = [
        'label' => $node->text(),
        'href' => $node->attr('href'),
      ];
    });

    self::assertEquals($expected, $actual);
  }

}
