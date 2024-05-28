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
    $labels = $this->extractLabels($expected_items, 'term');
    $values = $this->extractLabels($expected_items, 'definition');
    $dt_icons = $this->extractIcons($expected_items, 'term');
    $dd_icons = $this->extractIcons($expected_items, 'definition');

    $this->assertLabels($labels, $crawler);
    $this->assertIcons($dt_icons, 'dt', $crawler);
    $this->assertIcons($dd_icons, 'dd', $crawler);
    $this->assertValues($values, $crawler);
  }

  /**
   * Extracts labels from expected items.
   *
   * @param array $expected_items
   *   The expected item values.
   * @param string $key
   *   The key to extract labels for ('term' or 'definition').
   *
   * @return array
   *   The extracted labels.
   */
  private function extractLabels(array $expected_items, string $key): array {
    $labels = [];
    foreach ($expected_items as $item) {
      if (isset($item[$key])) {
        $labels = array_merge($labels, $this->extractLabel($item[$key]));
      }
    }
    return $labels;
  }

  /**
   * Extracts label from an item.
   *
   * @param mixed $item
   *   The item to extract label from.
   *
   * @return array
   *   The extracted labels.
   */
  private function extractLabel($item): array {
    $labels = [];
    if ($item === NULL) {
      return $labels;
    }

    if (is_array($item)) {
      foreach ($item as $subItem) {
        if (isset($subItem['label'])) {
          $labels[] = is_array($subItem['label']) ? strip_tags($subItem['label'][0]['#markup'] ?? $subItem['label'][0]) : strip_tags($subItem['label']);
        }
      }
    }
    elseif (is_string($item)) {
      $labels[] = strip_tags($item);
    }
    return $labels;
  }

  /**
   * Extracts icons from expected items.
   *
   * @param array $expected_items
   *   The expected item values.
   * @param string $key
   *   The key to extract icons for ('term' or 'definition').
   *
   * @return array
   *   The extracted icons.
   */
  private function extractIcons(array $expected_items, string $key): array {
    $icons = [];
    foreach ($expected_items as $item) {
      if (isset($item[$key])) {
        $icons = array_merge($icons, $this->extractIcon($item[$key]));
      }
    }
    return $icons;
  }

  /**
   * Extracts icons from an item.
   *
   * @param mixed $item
   *   The item to extract icons from.
   *
   * @return array
   *   The extracted icons.
   */
  private function extractIcon($item): array {
    $icons = [];
    if ($item === NULL) {
      return $icons;
    }

    if (is_array($item)) {
      foreach ($item as $subItem) {
        $icon = $subItem['icon'] ?? NULL;
        if ($icon) {
          $icons[] = $icon['name'] ?? $icon;
        }
      }
    }
    return $icons;
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
