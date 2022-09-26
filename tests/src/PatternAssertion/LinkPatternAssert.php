<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\PatternAssertion;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the link pattern.
 */
class LinkPatternAssert extends BasePatternAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions(string $variant): array {
    return [
      'label' => [
        [$this, 'assertElementText'],
        'a',
      ],
      'path' => [
        [$this, 'assertElementAttribute'],
        'a',
        'href',
      ],
      'icon' => [
        [$this, 'assertIcon'],
      ],
      'settings' => [
        [$this, 'assertSettings'],
      ],
      'attributes' => [
        [$this, 'assertAttributes'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function assertBaseElements(string $html, string $variant): void {
    $crawler = new Crawler($html);

    $this->assertElementExists('a', $crawler);
  }

  /**
   * Asserts the link icon.
   *
   * @param string $expected
   *   The expected settings.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertIcon(string $expected, Crawler $crawler): void {
    $svg = $crawler->filter('svg');
    self::assertCount(1, $svg);

    (new IconPatternAssert())->assertPattern([
      'name' => $expected,
      'size' => 's',
    ], $svg->outerHtml());
  }

  /**
   * Asserts the link pattern settings.
   *
   * @param array[] $expected
   *   The expected settings.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertSettings(array $expected, Crawler $crawler): void {
    if (!empty($expected['disabled'])) {
      $this->assertElementHasClass('disabled', 'a', $crawler);
      $this->assertElementAttribute('true', 'a', 'aria-disabled', $crawler);
    }
    else {
      $this->assertElementNotHasClass('disabled', 'a', $crawler);
      $this->assertElementAttribute(NULL, 'a', 'aria-disabled', $crawler);
    }

    if (!empty($expected['standalone'])) {
      $this->assertElementHasClass('standalone', 'a', $crawler);
    }
    else {
      $this->assertElementNotHasClass('standalone', 'a', $crawler);
    }

    if (isset($expected['icon_position'])) {
      $link = $crawler->filter('a')->getNode(0);

      // If no icon or no label is set then the position is irrelevant.
      if (count($link->childNodes) === 2) {
        $node_name = $expected['icon_position'] === 'before' ? 'svg' : '#text';
        self::assertSame($node_name, $link->firstChild->nodeName);
      }
    }

    // We are checking for the presence of the margins only if
    // remove_icon_spacers is not set, because it is not the scope here to
    // assert the icon rendering.
    if (!empty($expected['remove_icon_spacers'])) {
      $this->assertElementNotHasClass('ms-', 'svg', $crawler);
      $this->assertElementNotHasClass('me-', 'svg', $crawler);
    }
  }

  /**
   * Asserts the link pattern attributes.
   *
   * @param array[] $expected
   *   The expected settings.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertAttributes(array $expected, Crawler $crawler): void {
    foreach ($expected as $attribute => $value) {
      $this->assertElementAttribute($value, 'a', $attribute, $crawler);
    }
  }

}
