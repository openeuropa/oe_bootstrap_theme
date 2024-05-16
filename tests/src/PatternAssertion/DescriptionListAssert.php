<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\PatternAssertion;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the field list pattern.
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
    // Initialize arrays to store labels, values, and icons.
    $labels = [];
    $values = [];
    $icons = ['dt' => [], 'dd' => []];

    // Extract labels, values, and icons from expected items.
    foreach ($expected_items as $item) {
      $this->extractItemData($item, $labels, $values, $icons);
    }

    // Assert labels and values.
    $this->assertLabelsAndValues($labels, $values, $crawler);

    // Assert icons in <dt> and <dd> elements.
    foreach (['dt', 'dd'] as $element) {
      $this->assertIcons($icons[$element], $crawler->filter('div')->children($element)->filter('svg'), $element);
    }
  }

  /**
   * Extracts label, value, and icon data from an item.
   *
   * @param array $item
   *   The item data.
   * @param array $labels
   *   Array to store extracted labels.
   * @param array $values
   *   Array to store extracted values.
   * @param array $icons
   *   Array to store extracted icons.
   */
  private function extractItemData(array $item, array &$labels, array &$values, array &$icons): void {
    foreach (['term' => 'dt', 'definition' => 'dd'] as $key => $element) {
      $data = $item[$key] ?? NULL;
      if (is_array($data)) {
        foreach ($data as $subItem) {
          $this->extractSubItemData($subItem, $labels, $values, $icons[$element]);
        }
      }
      elseif (is_string($data)) {
        ${$element . 's'}[] = strip_tags($data);
      }
    }
  }

  /**
   * Extracts label, value, and icon data from a sub-item.
   *
   * @param array $subItem
   *   The sub-item data.
   * @param array $labels
   *   Array to store extracted labels.
   * @param array $values
   *   Array to store extracted values.
   * @param array $icons
   *   Array to store extracted icons.
   */
  private function extractSubItemData(array $subItem, array &$labels, array &$values, array &$icons): void {
    $label = $subItem['label'] ?? NULL;
    if ($label) {
      $labels[] = is_array($label) ? strip_tags($label[0]['#markup'] ?? $label[0]) : strip_tags($label);
    }
    $icon = $subItem['icon'] ?? NULL;
    if ($icon) {
      $icons[] = $icon['name'] ?? $icon;
    }
    $value = $subItem['value'] ?? NULL;
    if ($value) {
      $values[] = is_array($value) ? strip_tags($value[0]['#markup'] ?? $value[0]) : strip_tags($value);
    }
  }

  /**
   * Asserts labels and values.
   *
   * @param array $labels
   *   The array of expected labels.
   * @param array $values
   *   The array of expected values.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler object.
   */
  private function assertLabelsAndValues(array $labels, array $values, Crawler $crawler): void {
    $label_items = $crawler->filter('div')->children('dt');
    self::assertSameSize($labels, $label_items, 'Mismatch in label count.');
    foreach ($labels as $index => $expected_label) {
      self::assertEquals($expected_label, trim(strip_tags($label_items->eq($index)->text())), 'Label assertion failed.');
    }

    $value_items = $crawler->filter('div')->children('dd');
    self::assertCount(count($values), $value_items, 'Mismatch in value count.');
    foreach ($values as $index => $expected_value) {
      self::assertEquals($expected_value, trim(strip_tags($value_items->eq($index)->text())), 'Value assertion failed.');
    }
  }

  /**
   * Asserts icons.
   *
   * @param array $expected_icons
   *   The array of expected icons.
   * @param \Symfony\Component\DomCrawler\Crawler $icon_items
   *   The Crawler object containing icon items.
   * @param string $element
   *   The element type ('dt' or 'dd').
   */
  private function assertIcons(array $expected_icons, Crawler $icon_items, string $element): void {
    self::assertSameSize($expected_icons, $icon_items, "Mismatch in icon count ($element).");
    foreach ($expected_icons as $index => $expected_icon) {
      $expectedIcon = '<use xlink:href="/themes/custom/oe_bootstrap_theme/assets/icons/bcl-default-icons.svg#' . $expected_icon . '"></use>';
      self::assertTrue($icon_items->eq($index)->count() > 0, "Icon element not found ($element).");
      self::assertEquals($expectedIcon, $icon_items->eq($index)->html(), "Icon assertion failed ($element).");
    }
  }

}
