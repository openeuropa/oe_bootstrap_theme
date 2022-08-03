<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\PatternAssertion;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the icon pattern.
 */
class IconPatternAssert extends BasePatternAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions(string $variant): array {
    return [
      'name' => [
        [$this, 'assertIconPath'],
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
    // Verify that only one SVG markup has been passed.
    $this->assertElementExists('svg', new Crawler($html));
  }

  /**
   * Asserts the icon path.
   *
   * @param string $expected
   *   The expected icon name, used to build the path.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertIconPath(string $expected, Crawler $crawler): void {
    $expected_icon_markup = sprintf(
      '<use xlink:href="%s/assets/icons/bcl-default-icons.svg#%s"></use>',
      base_path() . \Drupal::service('extension.list.theme')->getPath('oe_bootstrap_theme'),
      $expected
    );
    self::assertEquals($expected_icon_markup, $crawler->filter('svg')->html());
  }

  /**
   * Asserts the icon size.
   *
   * @param string $expected
   *   The expected icon size.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertSize(string $expected, Crawler $crawler): void {
    $this->assertElementExists('svg.icon--' . $expected, $crawler);
  }

}
