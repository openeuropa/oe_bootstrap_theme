<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\PatternAssertion;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the badge pattern.
 */
class BadgePatternAssert extends BasePatternAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions(string $variant): array {
    return [
      'settings' => [
        [$this, 'assertSettings'],
      ],
      'label' => [
        [$this, 'assertLabel'],
      ],
      'url' => [
        [$this, 'assertElementAttribute'],
        '.badge',
        'href',
      ],
      'title' => [
        [$this, 'assertElementAttribute'],
        '.badge',
        'title',
      ],
      'assistive_text' => [
        [$this, 'assertAssistiveText'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function assertBaseElements(string $html, string $variant): void {
    $crawler = new Crawler($html);

    $this->assertElementExists('.badge', $crawler);
  }

  /**
   * Asserts the badge pattern settings.
   *
   * @param array[] $expected
   *   The expected settings.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertSettings(array $expected, Crawler $crawler): void {
    $background = $expected['background'] ?? 'primary';

    $this->assertBackground($background, $crawler);
    $this->assertOutline($expected['outline'] ?? FALSE, $crawler);
    $this->assertRoundedPill($expected['rounded_pill'] ?? FALSE, $crawler);
    $this->assertDismissible($expected['dismissible'] ?? FALSE, $crawler);
  }

  /**
   * Asserts the badge label.
   *
   * @param string $expected
   *   The expected label.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertLabel(string $expected, Crawler $crawler): void {
    $this->assertElementExists('.badge', $crawler);

    $badge = $crawler->filter('.badge');
    $actual = trim($badge->text());
    $assistive_text = $badge->filter('.visually-hidden');
    if ($assistive_text->count()) {
      $actual = preg_replace('/^' . preg_quote(trim($assistive_text->text()), '/') . '/', '', $actual);
    }

    self::assertEquals($expected, trim($actual));
  }

  /**
   * Asserts the badge background.
   *
   * @param string $expected
   *   The expected background.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertBackground(string $expected, Crawler $crawler): void {
    $this->assertElementExists('.badge-outline-' . $expected . ', .text-bg-' . $expected, $crawler);
  }

  /**
   * Asserts the badge outline.
   *
   * @param bool $expected
   *   Whether the badge should be outlined.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertOutline(bool $expected, Crawler $crawler): void {
    $badge = $crawler->filter('.badge');
    $classes = explode(' ', $badge->attr('class') ?? '');
    $background = 'primary';
    foreach ($classes as $class) {
      if (str_starts_with($class, 'badge-outline-') || str_starts_with($class, 'text-bg-')) {
        $background = preg_replace('/^(badge-outline|text-bg)-/', '', $class);
        break;
      }
    }

    if ($expected) {
      $this->assertElementExists('.badge-outline-' . $background, $crawler);
      $this->assertElementNotExists('.text-bg-' . $background, $crawler);
    }
    else {
      $this->assertElementNotExists('.badge-outline-' . $background, $crawler);
      $this->assertElementExists('.text-bg-' . $background, $crawler);
    }
  }

  /**
   * Asserts the badge rounded pill class.
   *
   * @param bool $expected
   *   Whether the badge should be a rounded pill.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertRoundedPill(bool $expected, Crawler $crawler): void {
    if ($expected) {
      $this->assertElementExists('.rounded-pill', $crawler);
    }
    else {
      $this->assertElementNotExists('.rounded-pill', $crawler);
    }
  }

  /**
   * Asserts the badge dismiss icon.
   *
   * @param bool $expected
   *   Whether the badge should be dismissible.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertDismissible(bool $expected, Crawler $crawler): void {
    if ($expected) {
      $this->assertElementExists('.icon--close', $crawler);
    }
    else {
      $this->assertElementNotExists('.icon--close', $crawler);
    }
  }

  /**
   * Asserts the visually hidden assistive text.
   *
   * @param string|null $expected
   *   The expected assistive text.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertAssistiveText(?string $expected, Crawler $crawler): void {
    $this->assertElementText($expected, '.badge .visually-hidden', $crawler);
  }

}
