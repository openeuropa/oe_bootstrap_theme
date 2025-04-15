<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\PatternAssertion;

use Drupal\oe_bootstrap_theme\BackwardCompatibility;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the card pattern.
 *
 * @see ./templates/patterns/card/card.ui_patterns.yml
 */
class CardPatternAssert extends BasePatternAssert {

  /**
   * Backward compatibility setting: use grid classes in search variant.
   *
   * @var bool
   */
  protected bool $cardSearchUseGridClasses;

  /**
   * Backward compatibility setting: image is hidden in search variant.
   *
   * @var bool
   */
  protected ?bool $cardSearchImageHideOnMobile;

  /**
   * Creates a new instance of this class.
   */
  public function __construct(?bool $cardSearchUseGridClasses = NULL, ?bool $cardSearchImageHideOnMobile = NULL) {
    $this->cardSearchUseGridClasses = $cardSearchUseGridClasses ?? BackwardCompatibility::getSetting('card_search_use_grid_classes');
    $this->cardSearchImageHideOnMobile = $cardSearchImageHideOnMobile ?? BackwardCompatibility::getSetting('card_search_image_hide_on_mobile');
  }

  /**
   * {@inheritdoc}
   */
  protected function getAssertions($variant): array {
    return [
      'horizontal' => [
        [$this, 'assertHorizontal'],
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
      'title_tag' => [
        [$this, 'assertElementTag'],
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
      'date' => [
        [$this, 'assertDate'],
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
    $selector = 'article.card img';

    if ($variant === 'search') {
      $selector = '.row '
        . ($this->cardSearchUseGridClasses ? '.col-md-3' : '.bcl-card-start-col')
        . ' img.card-img-top';
    }

    $image_element = $crawler->filter($selector);
    self::assertCount(1, $image_element);

    if ($variant === 'search') {
      self::assertCount((int) $this->cardSearchImageHideOnMobile, $crawler->filter($selector . '.d-none.d-md-block'));
    }

    self::assertEquals($expected_image['alt'], $image_element->attr('alt'));
    self::assertStringContainsString($expected_image['src'], $image_element->attr('src'));
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
        $selector = '.row ' . ($this->cardSearchUseGridClasses ? '.col-md-9.col-lg-8.col-xl-10' : '.col-12.col-md');
        self::assertStringContainsString($expected_item, $crawler->filter($selector)->html());
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
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertHorizontal(bool $expected, Crawler $crawler): void {
    if ($expected) {
      $this->assertElementExists('.row .col-4', $crawler);
      $this->assertElementExists('.row .col-8', $crawler);
      return;
    }
    $this->assertElementNotExists('.row .col-4', $crawler);
    $this->assertElementNotExists('.row .col-8', $crawler);
  }

  /**
   * Asserts the card date block.
   *
   * @param array $expected
   *   The expected data.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertDate(array $expected, Crawler $crawler): void {
    // Assert day(s) and month(s).
    $this->assertElementText($expected['month'], 'time > div > div > div:nth-of-type(1) .date-month', $crawler);
    $this->assertElementText($expected['day'], 'time > div > div > div:nth-of-type(1) .date-day', $crawler);

    if (isset($expected['end_day']) && isset($expected['end_month'])) {
      $this->assertElementText($expected['end_month'], 'time > div > div > div:nth-of-type(3) .date-month', $crawler);
      $this->assertElementText($expected['end_day'], 'time > div > div > div:nth-of-type(3) .date-day', $crawler);
    }

    // Assert year(s).
    $this->assertElementText($expected['year'], 'time > div > div:nth-of-type(2) .year-start', $crawler);
    if (isset($expected['end_year']) && $expected['year'] !== $expected['end_year']) {
      $this->assertElementText($expected['end_year'], 'time > div > div:nth-of-type(2) .year-end', $crawler);
    }

    // Assert datetime attribute.
    $this->assertElementAttribute($expected['date_time'] ?? NULL, 'time', 'datetime', $crawler);
  }

  /**
   * {@inheritdoc}
   */
  protected function assertBaseElements(string $html, string $variant): void {
    $crawler = new Crawler($html);
    $selector = 'article.card';

    if ($variant === 'search') {
      $selector = 'article.listing-item.card';
    }

    $card = $crawler->filter($selector);
    self::assertCount(1, $card);
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
