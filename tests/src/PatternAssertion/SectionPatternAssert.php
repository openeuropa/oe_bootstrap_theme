<?php

declare(strict_types=1);

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
        [$this, 'assertElementAttributes'],
        '.section',
      ],
      'heading_attributes' => [
        [$this, 'assertElementAttributes'],
        '.section__title',
      ],
      'wrapper_attributes' => [
        [$this, 'assertElementAttributes'],
        '.section__body',
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

}
