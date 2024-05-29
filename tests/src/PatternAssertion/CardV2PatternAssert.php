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
  protected function getAssertions(): array {
    return [
      'title' => [
        [$this, 'assertElementNormalisedHtml'],
        '.bcl-heading',
      ],
      'image' => [
        [$this, 'assertElementNormalisedHtml'],
        'img',
      ],
      'card_body_content' => [
        [$this, 'assertElementNormalisedHtml'],
        '.card-body',
      ],
      'card_footer_content' => [
        [$this, 'assertElementNormalisedHtml'],
        '.card-footer',
      ],
      'card_header_content' => [
        [$this, 'assertElementNormalisedHtml'],
        '.card-header',
      ],
      'attributes' => [
        [$this, 'assertWrapperAttributes'],
        '.card',
      ],
      'body_attributes' => [
        [$this, 'assertWrapperAttributes'],
        '.card-body',
      ],
      'header_attributes' => [
        [$this, 'assertWrapperAttributes'],
        '.card-header',
      ],
      'footer_attributes' => [
        [$this, 'assertWrapperAttributes'],
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
    $this->assertElementExists('.card > .card_body', $crawler);
    $this->assertElementExists('.card > .card_header', $crawler);
    $this->assertElementExists('.card > .card_footer', $crawler);
    $this->assertElementExists('.card > img', $crawler);
  }

  /**
   * Asserts the rendered html of a particular element.
   *
   * @param string|null $expected
   *   The expected value.
   * @param string $selector
   *   The CSS selector to find the element.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertElementNormalisedHtml(?string $expected, string $selector, Crawler $crawler): void {
    if (is_null($expected)) {
      $this->assertElementNotExists($selector, $crawler);
      return;
    }
    $this->assertElementExists($selector, $crawler);
    $element = $crawler->filter($selector);
    $html = trim(preg_replace('/\s+/u', ' ', $element->html()));
    self::assertEquals($expected, $html);
  }

  /**
   * Asserts the card v2 pattern wrapper attributes.
   *
   * @param string[] $expected
   *   The expected settings.
   * @param string $selector
   *   The CSS selector to find the element.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertWrapperAttributes(array $expected, string $selector, Crawler $crawler): void {
    foreach ($expected as $attribute => $value) {
      $this->assertElementAttribute($value, '.section__body', $attribute, $crawler);
    }
  }

}
