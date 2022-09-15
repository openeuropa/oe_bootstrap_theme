<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\PatternAssertion;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the badge pattern.
 */
class BadgePatternAssert extends BasePatternAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions(string $variant): array {
    return [
      'settings' => [
        [$this, 'assertSettings'],
      ],
      'label' => [
        [$this, 'assertElementText'],
        '.badge',
      ],
      'url' => [
        [$this, 'assertElementAttribute'],
        '.badge',
        'href',
      ],
      'title' => [
        [$this, 'assertElementAttribute'],
        '.badge',
        'title',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function assertBaseElements(string $html, string $variant): void {
    $crawler = new Crawler($html);

    $this->assertElementExists('.badge', $crawler);
  }

  /**
   * Asserts the badge pattern settings.
   *
   * @param array[] $expected
   *   The expected settings.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertSettings(array $expected, Crawler $crawler): void {
    $background = $expected['background'] ?? 'primary';

    if (!empty($expected['outline'])) {
      $this->assertElementExists('.badge-outline-' . $background, $crawler);
    }
    else {
      $this->assertElementExists('.bg-' . $background, $crawler);
    }

    if (!empty($expected['rounded_pill'])) {
      $this->assertElementExists('.rounded-pill', $crawler);
    }
    else {
      $this->assertElementNotExists('.rounded-pill', $crawler);
    }

    if (!empty($expected['dismissible'])) {
      $this->assertElementExists('.icon--close', $crawler);
    }
    else {
      $this->assertElementNotExists('.icon--close', $crawler);
    }
  }

}
