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
        [$this, 'assertCardMedia'],
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
    $this->assertElementExists('.card > .card-body', $crawler);
    $this->assertElementExists('.card > .card-media', $crawler);
    $this->assertElementExists('.card > .card-header', $crawler);
    $this->assertElementExists('.card > .card-footer', $crawler);
    if ($variant === 'horizontal') {
      $this->assertElementExists('body > .card.horizontal', $crawler);
    }
  }

  /**
   * Asserts the media of a card.
   *
   * @param string $expected_media
   *   The expected media values.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertCardMedia(string $expected_media, Crawler $crawler): void {
    $selector = '.card-media';

    $image_element = $crawler->filter($selector);
    self::assertCount(1, $image_element);

    $html = trim(preg_replace('/\s+/u', ' ', $image_element->html()));

    self::assertEquals($expected_media, $html);
  }

}
