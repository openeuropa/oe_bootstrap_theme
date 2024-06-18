<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\PatternAssertion;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the card v2 pattern.
 */
class CardV2PatternAssert extends BasePatternAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions($variant): array {
    return [
      'title' => [
        [$this, 'assertElementNormalisedHtml'],
        '.card-title',
      ],
      'media' => [
        [$this, 'assertElementNormalisedHtml'],
        '.card-media',
      ],
      'tag' => [
        [$this, 'assertElementTag'],
        '.card',
      ],
      'content' => [
        [$this, 'assertElementNormalisedHtml'],
        '.card-body',
      ],
      'footer' => [
        [$this, 'assertElementNormalisedHtml'],
        '.card-footer',
      ],
      'header' => [
        [$this, 'assertElementNormalisedHtml'],
        '.card-header',
      ],
      'attributes' => [
        [$this, 'assertElementAttributes'],
        '.card',
      ],
      'media_attributes' => [
        [$this, 'assertElementAttributes'],
        '.card-media',
      ],
      'content_attributes' => [
        [$this, 'assertElementAttributes'],
        '.card-body',
      ],
      'header_attributes' => [
        [$this, 'assertElementAttributes'],
        '.card-header',
      ],
      'footer_attributes' => [
        [$this, 'assertElementAttributes'],
        '.card-footer',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function assertBaseElements(string $html, string $variant): void {
    $crawler = new Crawler($html);
    $this->assertElementExists('body > .card', $crawler);
  }

  /**
   * {@inheritdoc}
   */
  protected function getPatternVariant(string $html): string {
    $crawler = new Crawler($html);

    return $crawler->filter('body > .card.horizontal')->count() ? 'horizontal' : 'default';
  }

}
