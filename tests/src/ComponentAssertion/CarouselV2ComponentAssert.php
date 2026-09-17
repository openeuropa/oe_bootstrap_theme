<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\ComponentAssertion;

use Drupal\Tests\oe_bootstrap_theme\PatternAssertion\BasePatternAssert;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the carousel V2 component.
 */
class CarouselV2ComponentAssert extends BasePatternAssert {

  /**
   * {@inheritdoc}
   */
  protected function getPatternVariant(string $html): string {
    $crawler = new Crawler($html);
    $root = $crawler->filter('.bcl-carousel-v2');
    if ($root->count() && $root->attr('class') && str_contains($root->attr('class'), 'bcl-carousel-v2--full_width')) {
      return 'full_width';
    }
    return 'split';
  }

  /**
   * {@inheritdoc}
   */
  protected function getAssertions(string $variant): array {
    return [
      'items' => [
        [$this, 'assertItems'],
      ],
      'label' => [
        [$this, 'assertElementAttribute'],
        '.bcl-carousel-v2',
        'aria-label',
      ],
      'carousel_role_label' => [
        [$this, 'assertElementAttribute'],
        '.bcl-carousel-v2',
        'aria-roledescription',
      ],
      'slide_role_label' => [
        [$this, 'assertSlideRoleLabel'],
      ],
      'active_item' => [
        [$this, 'assertActiveItem'],
      ],
      'slide_label' => [
        [$this, 'assertSlideLabel'],
      ],
      'settings' => [
        [$this, 'assertSettings'],
      ],
      'controls' => [
        [$this, 'assertControls'],
      ],
      'attributes' => [
        [$this, 'assertElementAttributes'],
        '.bcl-carousel-v2',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function assertBaseElements(string $html, string $variant): void {
    $crawler = new Crawler($html);

    $this->assertElementExists('.bcl-carousel-v2 .carousel-inner', $crawler);
    $this->assertElementExists('.bcl-carousel-v2.bcl-carousel-v2--' . $variant, $crawler);
    $this->assertElementAttribute('region', '.bcl-carousel-v2', 'role', $crawler);
    // The V2 controller owns rotation; Bootstrap's own data-API must be off.
    $this->assertElementAttribute('false', '.bcl-carousel-v2', 'data-bs-ride', $crawler);
    $this->assertElementAttribute('false', '.bcl-carousel-v2', 'data-bs-pause', $crawler);
  }

  /**
   * Asserts the active item index.
   *
   * @param int $expected
   *   The 1-based index of the expected active slide.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertActiveItem(int $expected, Crawler $crawler): void {
    $items = $crawler->filter('.bcl-carousel-v2 .carousel-inner .carousel-item');
    foreach ($items as $index => $item) {
      $is_active = str_contains($item->getAttribute('class'), 'active');
      self::assertSame($index + 1 === $expected, $is_active, sprintf('Unexpected active state for item %d.', $index + 1));
    }
    // The counter only exists alongside the other navigation controls.
    if ($items->count() > 1) {
      $this->assertElementText((string) $expected, '[data-bcl-current]', $crawler);
    }
  }

  /**
   * Asserts the aria-label of the active slide.
   *
   * @param string $expected
   *   The expected aria-label of the currently active slide.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertSlideLabel(string $expected, Crawler $crawler): void {
    $this->assertElementAttribute($expected, '.carousel-item.active', 'aria-label', $crawler);
  }

  /**
   * Asserts the aria-roledescription of every slide.
   *
   * @param string $expected
   *   The expected aria-roledescription for each slide.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertSlideRoleLabel(string $expected, Crawler $crawler): void {
    $items = $crawler->filter('.bcl-carousel-v2 .carousel-inner .carousel-item');
    foreach ($items as $index => $item) {
      self::assertSame($expected, $item->getAttribute('aria-roledescription'), sprintf('Unexpected slide role label for item %d.', $index + 1));
    }
  }

  /**
   * Asserts the carousel V2 settings.
   *
   * @param array $expected
   *   The expected settings.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertSettings(array $expected, Crawler $crawler): void {
    $expected_autoplay = !empty($expected['autoplay']) ? 'true' : 'false';
    $this->assertElementAttribute($expected_autoplay, '.bcl-carousel-v2', 'data-bcl-autoplay', $crawler);

    if (isset($expected['interval'])) {
      $this->assertElementAttribute((string) $expected['interval'], '.bcl-carousel-v2', 'data-bs-interval', $crawler);
    }

    $expected_disable_touch = !empty($expected['disable_touch']) ? 'false' : 'true';
    $this->assertElementAttribute($expected_disable_touch, '.bcl-carousel-v2', 'data-bs-touch', $crawler);
  }

  /**
   * Asserts the carousel V2 navigation controls.
   *
   * @param array|null $expected
   *   The expected control labels, keyed by 'prev', 'next', 'play', 'pause'.
   *   NULL when a single slide is expected to hide the controls.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertControls(?array $expected, Crawler $crawler): void {
    if ($expected === NULL) {
      $this->assertElementNotExists('.bcl-carousel-v2__controls', $crawler);
      return;
    }

    $this->assertElementAttribute($expected['prev'], '[data-bs-slide="prev"]', 'aria-label', $crawler);
    $this->assertElementAttribute($expected['next'], '[data-bs-slide="next"]', 'aria-label', $crawler);
    $this->assertElementAttribute($expected['play'], '[data-bcl-rotation]', 'aria-label', $crawler);
    $this->assertElementAttribute($expected['play'], '[data-bcl-rotation]', 'data-bcl-play-label', $crawler);
    $this->assertElementAttribute($expected['pause'], '[data-bcl-rotation]', 'data-bcl-pause-label', $crawler);
  }

  /**
   * Asserts the carousel V2 slides.
   *
   * @param array[] $expected
   *   The expected item values.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertItems(array $expected, Crawler $crawler): void {
    $items = $crawler->filter('.bcl-carousel-v2 .carousel-inner .carousel-item');
    self::assertSameSize($expected, $items);

    foreach ($expected as $index => $expected_item) {
      $item = $items->eq($index);

      try {
        // The interval attribute is only rendered for a positive value.
        $expected_interval = !empty($expected_item['interval']) ? (string) $expected_item['interval'] : NULL;
        $this->assertElementAttribute($expected_interval, '.carousel-item', 'data-bs-interval', $item);

        if (isset($expected_item['image_alt'])) {
          $this->assertElementAttribute($expected_item['image_alt'], '.bcl-carousel-v2__image img', 'alt', $item);
          foreach (['title', 'loading', 'width', 'height', 'class'] as $attribute) {
            if (isset($expected_item['image_' . $attribute])) {
              $this->assertElementAttribute($expected_item['image_' . $attribute], '.bcl-carousel-v2__image img', $attribute, $item);
            }
          }
        }
        else {
          $this->assertElementNotExists('.bcl-carousel-v2__image img', $item);
        }
        $heading_selector = implode(', ', array_map(
          fn (string $tag) => '.bcl-carousel-v2__content > ' . $tag,
          ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
        ));
        $this->assertElementText($expected_item['caption_title'] ?? NULL, $heading_selector, $item);
        if (isset($expected_item['caption'])) {
          $this->assertElementTextContains($expected_item['caption'], '.bcl-carousel-v2__description', $item);
        }
        if (isset($expected_item['link'])) {
          $this->assertElementText($expected_item['link']['label'], '.bcl-carousel-v2__content a', $item);
          $this->assertElementAttribute($expected_item['link']['path'], '.bcl-carousel-v2__content a', 'href', $item);
          $this->assertElementExists('.bcl-carousel-v2__content a svg', $item);
        }
        else {
          $this->assertElementNotExists('.bcl-carousel-v2__content a', $item);
        }
        if (isset($expected_item['copyright'])) {
          $this->assertElementTextContains($expected_item['copyright'], '.bcl-carousel-v2__copyright', $item);
          $this->assertElementText($expected_item['copyright_label'] ?? 'Image credit:', '.bcl-carousel-v2__copyright .visually-hidden', $item);
        }
        else {
          $this->assertElementNotExists('.bcl-carousel-v2__copyright', $item);
        }
      }
      catch (\Exception $e) {
        throw new \Exception(sprintf('Failed asserting data for item %s.', $index), 0, $e);
      }
    }
  }

}
