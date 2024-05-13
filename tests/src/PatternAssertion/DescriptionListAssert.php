<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\PatternAssertions;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the field list pattern.
 *
 * @see ./templates/patterns/field_list/field_list.ui_patterns.yml
 */
class DescriptionListAssert extends BasePatternAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions(string $variant): array {
    return [
      'items' => [
        [$this, 'assertItems'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function assertBaseElements(string $html, string $variant): void {
    $this->assertElementExists('body > .bcl-description-list', new Crawler($html));
  }

  /**
   * Asserts the items of the pattern.
   *
   * @param array $expected_items
   *   The expected item values.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertItems(array $expected_items, Crawler $crawler): void {
    // Assert all labels are correct.
    $expected_labels = array_column($expected_items, 'term');
    $label_items = $crawler->filter('dt');
    self::assertSameSize($expected_labels, $label_items);
    foreach ($expected_labels as $index => $expected_label) {
      self::assertEquals($expected_label, trim($label_items->eq($index)->text()));
    }

    // Assert all values are correct.
    $expected_values = array_column($expected_items, 'definition');
    $value_items = $crawler->filter('dd');
    self::assertCount(count($expected_labels), $value_items);
    foreach ($expected_values as $index => $expected_value) {
      self::assertEquals($expected_value, trim($value_items->eq($index)->text()));
    }
  }

}
