<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\PatternAssertion;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the section pattern.
 */
class SectionPatternAssert extends BasePatternAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions(string $variant): array {
    return [
      'heading' => [
        [$this, 'assertElementNormalisedHtml'],
        '.section__title',
      ],
      'content' => [
        [$this, 'assertElementNormalisedHtml'],
        '.section__body',
      ],
      'tag' => [
        [$this, 'assertElementTag'],
        '.section',
      ],
      'heading_tag' => [
        [$this, 'assertElementTag'],
        '.section__title',
      ],
      'attributes' => [
        [$this, 'assertAttributes'],
      ],
      'heading_attributes' => [
        [$this, 'assertHeadingAttributes'],
      ],
      'wrapper_attributes' => [
        [$this, 'assertWrapperAttributes'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function assertBaseElements(string $html, string $variant): void {
    $crawler = new Crawler($html);
    $this->assertElementExists('body > .section', $crawler);
    $this->assertElementExists('.section > .section__body', $crawler);
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
   * Asserts the section pattern attributes.
   *
   * @param string[] $expected
   *   The expected settings.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertAttributes(array $expected, Crawler $crawler): void {
    foreach ($expected as $attribute => $value) {
      $this->assertElementAttribute($value, '.section', $attribute, $crawler);
    }
  }

  /**
   * Asserts the section pattern heading attributes.
   *
   * @param string[] $expected
   *   The expected settings.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertHeadingAttributes(array $expected, Crawler $crawler): void {
    foreach ($expected as $attribute => $value) {
      $this->assertElementAttribute($value, '.section__title', $attribute, $crawler);
    }
  }

  /**
   * Asserts the section pattern wrapper attributes.
   *
   * @param string[] $expected
   *   The expected settings.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertWrapperAttributes(array $expected, Crawler $crawler): void {
    foreach ($expected as $attribute => $value) {
      $this->assertElementAttribute($value, '.section__body', $attribute, $crawler);
    }
  }

}
