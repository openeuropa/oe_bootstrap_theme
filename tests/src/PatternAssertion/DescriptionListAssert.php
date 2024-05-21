<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\PatternAssertion;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the description list pattern.
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
    $crawler = new Crawler($html);
    $description_list = $crawler->filter('body > .bcl-description-list');
    self::assertCount(1, $description_list);
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
    $dt_icons = [];
    $dd_icons = [];

    // Extract labels, values, and icons from expected items.
    $this->extractLabelsValuesAndIcons($expected_items, $labels, $values, $dt_icons, $dd_icons);

    // Assert labels.
    $this->assertLabels($labels, $crawler);

    // Assert icons in <dt> elements.
    $this->assertIcons($dt_icons, 'dt', $crawler);

    // Assert icons in <dd> elements.
    $this->assertIcons($dd_icons, 'dd', $crawler);

    // Assert values.
    $this->assertValues($values, $crawler);
  }

  /**
   * Extracts labels, values, and icons from expected items.
   *
   * @param array $expected_items
   *   The expected item values.
   * @param array $labels
   *   An array to store labels.
   * @param array $values
   *   An array to store values.
   * @param array $dt_icons
   *   An array to store icons for <dt> elements.
   * @param array $dd_icons
   *   An array to store icons for <dd> elements.
   */
  private function extractLabelsValuesAndIcons(array $expected_items, array &$labels, array &$values, array &$dt_icons, array &$dd_icons): void {
    foreach ($expected_items as $item) {
      $this->extractLabelsAndIcons($item['term'] ?? NULL, $labels, $dt_icons);
      $this->extractLabelsAndIcons($item['definition'] ?? NULL, $values, $dd_icons);
    }
  }

  /**
   * Extracts labels and icons from an item.
   *
   * @param mixed $item
   *   The item to extract labels and icons from.
   * @param array $labels
   *   An array to store labels.
   * @param array $icons
   *   An array to store icons.
   */
  private function extractLabelsAndIcons($item, array &$labels, array &$icons): void {
    if ($item === NULL) {
      return;
    }

    if (is_array($item)) {
      foreach ($item as $subItem) {
        if (isset($subItem['label'])) {
          $labels[] = is_array($subItem['label']) ? strip_tags($subItem['label'][0]['#markup'] ?? $subItem['label'][0]) : strip_tags($subItem['label']);
        }
        // Extract icon if provided.
        $icon = $subItem['icon'] ?? NULL;
        if ($icon) {
          $icons[] = $icon['name'] ?? $icon;
        }
      }
    }
    elseif (is_string($item)) {
      $labels[] = strip_tags($item);
    }
  }

  /**
   * Asserts labels against the DOM crawler.
   *
   * @param array $labels
   *   An array of labels.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DOM crawler.
   */
  private function assertLabels(array $labels, Crawler $crawler): void {
    $label_items = $crawler->filter('div')->children('dt');
    self::assertSameSize($labels, $label_items, 'Mismatch in label count.');
    foreach ($labels as $index => $expected_label) {
      self::assertEquals($expected_label, trim(strip_tags($label_items->eq($index)->text())), 'Label assertion failed.');
    }
  }

  /**
   * Asserts icons against the DOM crawler.
   *
   * @param array $icons
   *   An array of icons.
   * @param string $element
   *   The HTML element containing the icons.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DOM crawler.
   */
  private function assertIcons(array $icons, string $element, Crawler $crawler): void {
    $icon_items = $crawler->filter('div')->children($element)->filter('svg');
    self::assertSameSize($icons, $icon_items, "Mismatch in icon count ($element).");
    foreach ($icons as $index => $expected_icon) {
      $expectedIcon = '<use xlink:href="/themes/custom/oe_bootstrap_theme/assets/icons/bcl-default-icons.svg#' . $expected_icon . '"></use>';
      self::assertTrue($icon_items->eq($index)->count() > 0, "Icon element not found ($element).");
      self::assertEquals($expectedIcon, $icon_items->eq($index)->html(), "Icon assertion failed ($element).");
    }
  }

  /**
   * Asserts values against the DOM crawler.
   *
   * @param array $values
   *   An array of values.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DOM crawler.
   */
  private function assertValues(array $values, Crawler $crawler): void {
    $value_items = $crawler->filter('div')->children('dd');
    self::assertCount(count($values), $value_items, 'Mismatch in value count.');
    foreach ($values as $index => $expected_value) {
      self::assertEquals($expected_value, trim(strip_tags($value_items->eq($index)->text())), 'Value assertion failed.');
    }
  }

}
