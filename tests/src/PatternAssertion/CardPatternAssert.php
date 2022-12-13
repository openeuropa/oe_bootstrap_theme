<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\PatternAssertion;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the card pattern.
 *
 * @see ./templates/patterns/card/card.ui_patterns.yml
 */
class CardPatternAssert extends BasePatternAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions($variant): array {
    return [
      'horizontal' => [
        [$this, 'assertHorizontal'],
        $variant,
      ],
      'header' => [
        [$this, 'assertElementText'],
        '.card-header',
      ],
      'footer' => [
        [$this, 'assertElementText'],
        '.card-footer',
      ],
      'title' => [
        [$this, 'assertElementText'],
        '.card-title',
      ],
      'subtitle' => [
        [$this, 'assertElementText'],
        '.card-subtitle',
      ],
      'url' => [
        [$this, 'assertElementAttribute'],
        '.card-title a',
        'href',
      ],
      'image' => [
        [$this, 'assertCardImage'],
        $variant,
      ],
      'description' => [
        [$this, 'assertElementText'],
        '.card-text',
      ],
      'badges' => [
        [$this, 'assertBadgesElements'],
      ],
      'content' => [
        [$this, 'assertContent'],
        $variant,
      ],
    ];
  }

  /**
   * Asserts the image of a card.
   *
   * @param array|null $expected_image
   *   The expected image values.
   * @param string $variant
   *   The variant of the pattern being checked.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertCardImage($expected_image, string $variant, Crawler $crawler): void {
    if ($variant === 'search') {
      $image_div = $crawler->filter('.row .col-md-3 img.card-img-top');
      self::assertEquals($expected_image['alt'], $image_div->attr('alt'));
      self::assertStringContainsString($expected_image['src'], $image_div->attr('src'));
    }
    else {
      $image_div = $crawler->filter('article.card img');
      self::assertEquals($expected_image['alt'], $image_div->attr('alt'));
      self::assertStringContainsString($expected_image['src'], $image_div->attr('src'));
    }
  }

  /**
   * Asserts the content of a card.
   *
   * @param array $expected_items
   *   The expected item values.
   * @param string $variant
   *   The variant of the pattern being checked.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertContent(array $expected_items, string $variant, Crawler $crawler): void {
    // There's no wrapping element in content that can be targeted,
    // so we are checking that the expected items are present.
    foreach ($expected_items as $expected_item) {
      if ($variant === 'search') {
        self::assertStringContainsString($expected_item, $crawler->filter('.row .col-md-9')->html());
      }
      else {
        self::assertStringContainsString($expected_item, $crawler->html());
      }
    }
  }

  /**
   * Asserts if the card is configured as horizontal or not.
   *
   * @param bool $expected
   *   The expected state.
   * @param string $variant
   *   The variant of the pattern being checked.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertHorizontal(bool $expected, string $variant, Crawler $crawler): void {
    if ($expected) {
      $this->assertElementExists('.row .col-4', $crawler);
      $this->assertElementExists('.row .col-8', $crawler);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function assertBaseElements(string $html, string $variant): void {
    $crawler = new Crawler($html);
    $card = $crawler->filter($this->getBaseItemClass($variant));
    self::assertCount(1, $card);
  }

  /**
   * Returns the base CSS selector for a list item depending on the variant.
   *
   * @param string $variant
   *   The variant being checked.
   *
   * @return string
   *   The base selector for the variant.
   */
  protected function getBaseItemClass(string $variant): string {
    switch ($variant) {
      case 'search':
        return 'article.listing-item.card';

      default:
        return 'article.card';
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getPatternVariant(string $html): string {
    $crawler = new Crawler($html);
    if ($crawler->filter('article.listing-item')->count() > 0) {
      return 'search';
    }
    return 'default';
  }

}
