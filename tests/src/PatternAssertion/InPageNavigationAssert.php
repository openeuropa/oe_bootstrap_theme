<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\PatternAssertion;

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
        'h2.bcl-heading',
      ],
      'links' => [
        [$this, 'assertList'],
      ],
      'id' => [
        [$this, 'assertElementAttribute'],
        '.bcl-inpage-navigation',
        'id',
      ],
      'dropdown_id' => [
        [$this, 'assertElementAttribute'],
        '.dropdown-toggle',
        'id',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function assertBaseElements(string $html, string $variant): void {
    $crawler = new Crawler($html);

    $inpage_nav = $crawler->filter('ul.nav-pills');
    self::assertCount(1, $inpage_nav);

    $svg_icon = $crawler->filter('h2.bcl-heading svg');
    self::assertCount(1, $svg_icon);

    $base_element = $crawler->filter('.bcl-inpage-navigation');
    self::assertStringNotContainsString('d-none', $base_element->attr('class'));
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
    $actual = [];
    $crawler->filter('ul.nav-pills a.nav-link')->each(function (Crawler $node) use (&$actual) {
      $actual[] = [
        'label' => $node->text(),
        'href' => $node->attr('href'),
      ];
    });

    self::assertEquals($expected, $actual);
  }

}
