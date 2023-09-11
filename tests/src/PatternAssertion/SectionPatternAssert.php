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
      'tag' => [
        [$this, 'assertElementTag'],
        '.section__body',
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
    // Verify that only one SVG markup has been passed.
    $this->assertElementExists('.section', new Crawler($html));
  }

  /**
   * Asserts the section pattern attributes.
   *
   * @param array[] $expected
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
   * @param array[] $expected
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
   * @param array[] $expected
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
