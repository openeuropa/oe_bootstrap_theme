<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\PatternAssertion;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the pagination pattern.
 */
class PaginationPatternAssert extends BasePatternAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions(string $variant): array {
    return [
      'links' => [
        [$this, 'assertLinks'],
      ],
      'alignment' => [
        [$this, 'assertAlignment'],
      ],
      'size' => [
        [$this, 'assertSize'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function assertBaseElements(string $html, string $variant): void {
    $this->assertCounts([
      'nav' => 1,
      'ul' => 1,
    ], new Crawler($html));
  }

  /**
   * Asserts the pager links.
   *
   * @param array[] $expected
   *   Expected pager links.
   *   Each link is an array, with the following keys:
   *     - 'url': The link destination.
   *     - 'label': The label, if exists.
   *     - 'icon': The name of the icon.
   *     - 'active': Whether the item has an 'active' class.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertLinks(array $expected, Crawler $crawler): void {
    $expected_bcl_icon_url = '/' . \Drupal::service('extension.list.theme')->getPath('oe_bootstrap_theme') . '/assets/icons/bcl-default-icons.svg';

    $actual_links_data = array_map(
      function (\DOMElement $actual_li_element) use ($expected_bcl_icon_url) {
        $li = new Crawler($actual_li_element);
        $link = $li->filter('a');
        $actual_link_data = [];
        $actual_link_data['url'] = $link->attr('href');
        $use = $li->filter('a > svg > use');
        if ($use->count()) {
          // This is an icon.
          $icon_url = $use->attr('xlink:href');
          [$actual_bcl_icon_url, $actual_link_data['icon']] = explode('#', $icon_url, 2) + [NULL];
          $this->assertSame($expected_bcl_icon_url, $actual_bcl_icon_url);
        }
        else {
          $actual_link_data['label'] = $link->html();
        }
        if ($li->filter('li.active')->count()) {
          $actual_link_data['active'] = TRUE;
        }
        return $actual_link_data;
      },
      iterator_to_array($crawler->filter('nav > ul > li'), FALSE),
    );

    $this->assertSame($expected, $actual_links_data);

    $expected_count = count($expected);
    $this->assertCounts([
      'li' => $expected_count,
      'a' => $expected_count,
      'nav > ul > li.page-item > a.page-link:first-child' => $expected_count,
    ], $crawler);
  }

  /**
   * Asserts the alignment.
   *
   * @param string $expected
   *   The expected alignment. One of 'start', 'end' or 'center'.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertAlignment(string $expected, Crawler $crawler): void {
    $this->assertElementExists('nav > ul.justify-content-' . $expected, $crawler);
  }

  /**
   * Asserts the icon size.
   *
   * @param string $expected
   *   The expected icon size. Either 'sm' or 'lg'.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertSize(string $expected, Crawler $crawler): void {
    $this->assertElementExists('nav > ul.pagination-' . $expected, $crawler);
  }

}
