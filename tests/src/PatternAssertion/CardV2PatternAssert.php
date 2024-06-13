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

  /**
   * Asserts the attributes of a particular element.
   *
   * @param array $expected_attributes
   *   The expected attributes and their values.
   * @param string $selector
   *   The CSS selector to find the element.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertElementAttributes(array $expected_attributes, string $selector, Crawler $crawler): void {
    $this->assertElementExists($selector, $crawler);
    $element = $crawler->filter($selector);

    foreach ($expected_attributes as $attribute => $value) {
      if ($attribute === 'class') {
        $this->assertClassAttribute($value, $element);
      }
      else {
        self::assertEquals($value, $element->attr($attribute));
      }
    }
  }

  /**
   * Asserts the class attribute contains the expected classes.
   *
   * @param string $expected_classes
   *   The expected classes.
   * @param \Symfony\Component\DomCrawler\Crawler $element
   *   The DomCrawler element.
   */
  protected function assertClassAttribute(string $expected_classes, Crawler $element): void {
    $classes = explode(' ', $expected_classes);
    $element_classes = explode(' ', $element->attr('class'));

    foreach ($classes as $class) {
      self::assertContains($class, $element_classes);
    }
  }

}
