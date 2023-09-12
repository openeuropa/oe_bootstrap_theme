<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel\BackwardCompatibility;

use Drupal\Tests\oe_bootstrap_theme\Kernel\AbstractKernelTestBase;
use Drupal\Tests\oe_bootstrap_theme\Traits\BackwardCompatibilityTrait;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests backward compatibility for featured media pattern.
 */
class FeaturedMediaPatternTest extends AbstractKernelTestBase {

  use BackwardCompatibilityTrait;

  /**
   * Tests the backward compatibility settings for featured media.
   *
   * The test is covering the original (removed) markup rendering test cases
   * for featured media legacy pattern.
   */
  public function testBackwardCompatibility(): void {
    // Test legacy pattern.
    $this->setBackwardCompatibilitySetting('featured_media_use_legacy_pattern', TRUE);
    $this->coverImageNotWrapper();
    $this->coverImageWithTextAlignedLeft();
    $this->coverImageWithTextAlignedRight();
    $this->coverIframeNotWrapper();
    $this->coverIframeWithTextAlignedLeft();
    $this->coverIframeWithTextAlignedRightWithLinkRatio21x9();
  }

  /**
   * Covers 'only with image' case.
   */
  protected function coverImageNotWrapper(): void {
    $crawler = new Crawler($this->getPatternHtml());
    $this->assertCount(1, $crawler->filter('div.bcl-featured-media'));
    $this->assertCount(1, $crawler->filter('div.row'));
    $this->assertCount(1, $crawler->filter('div.col-12.col-md-4'));
    $this->assertCount(1, $crawler->filter('figure'));
    $this->assertCount(1, $crawler->filter('figcaption'));
    $this->assertCount(1, $crawler->filter('img'));
    $this->assertCount(1, $crawler->filter('img[src="https://picsum.photos/1200/600/"][alt="Alternative text for paragraph image"][title="Example"]'));
    $this->assertCount(1, $crawler->filter('div.bcl-featured-media > div.row'));
    $this->assertCount(1, $crawler->filter('div.bcl-featured-media > div.row > div.col-12.col-md-4'));
    $this->assertCount(1, $crawler->filter('div.bcl-featured-media > div.row > div.col-12.col-md-4 > figure'));
    $this->assertCount(1, $crawler->filter('figure > img.img-fluid'));
    $this->assertCount(1, $crawler->filter('figure > figcaption.bg-light.p-3'));
    $this->assertCount(0, $crawler->filter('h2'));
    $this->assertCount(0, $crawler->filter('h5'));
    $this->assertCount(0, $crawler->filter('a'));
    $this->assertCount(0, $crawler->filter('.md-6,.order-md-1,.order-md-2'));
    $this->assertElementText('Media description text goes here.', 'figcaption', $crawler);
  }

  /**
   * Covers 'with image, text aligned left' case.
   */
  protected function coverImageWithTextAlignedLeft(): void {
    $crawler = new Crawler($this->getPatternHtml(TRUE));
    $this->assertCount(1, $crawler->filter('div.bcl-featured-media'));
    $this->assertCount(1, $crawler->filter('div.row'));
    $this->assertCount(1, $crawler->filter('div.col-12.col-md-6.order-md-1'));
    $this->assertCount(1, $crawler->filter('div.col-12.col-md-6.order-md-2'));
    $this->assertCount(1, $crawler->filter('figure'));
    $this->assertCount(1, $crawler->filter('figcaption'));
    $this->assertCount(1, $crawler->filter('img'));
    $this->assertCount(1, $crawler->filter('img[src="https://picsum.photos/1200/600/"][alt="Alternative text for paragraph image"][title="Example"]'));
    $this->assertCount(1, $crawler->filter('div.bcl-featured-media > h2.mb-4'));
    $this->assertCount(1, $crawler->filter('div.bcl-featured-media > div.row'));
    $this->assertCount(1, $crawler->filter('div.bcl-featured-media > div.row > div.col-12.col-md-6.order-md-1'));
    $this->assertCount(1, $crawler->filter('div.bcl-featured-media > div.row > div.col-12.col-md-6.order-md-2'));
    $this->assertCount(1, $crawler->filter('div.bcl-featured-media > div.row > div.col-12.col-md-6.order-md-2 > figure'));
    $this->assertCount(1, $crawler->filter('figure > img.img-fluid'));
    $this->assertCount(1, $crawler->filter('figure > figcaption.bg-light.p-3'));
    $this->assertCount(1, $crawler->filter('h2.mb-4'));
    $this->assertCount(0, $crawler->filter('h5'));
    $this->assertCount(0, $crawler->filter('a'));
    $this->assertCount(0, $crawler->filter('.md-6'));
    $this->assertElementText('Title', 'h2', $crawler);
    $this->assertElementText('Lorem ipsum dolor sit amet.', 'div.col-12.col-md-6.order-md-1', $crawler);
    $this->assertElementText('Media description text goes here.', 'figcaption', $crawler);
  }

  /**
   * Covers 'with image, text aligned right' case.
   */
  protected function coverImageWithTextAlignedRight(): void {
    $crawler = new Crawler($this->getPatternHtml(TRUE, 'right'));
    $this->assertCount(1, $crawler->filter('div.bcl-featured-media'));
    $this->assertCount(1, $crawler->filter('div.row'));
    $this->assertCount(1, $crawler->filter('div.col-12.col-md-6.order-md-1'));
    $this->assertCount(1, $crawler->filter('div.col-12.col-md-6.order-md-2'));
    $this->assertCount(1, $crawler->filter('figure'));
    $this->assertCount(1, $crawler->filter('figcaption'));
    $this->assertCount(1, $crawler->filter('img'));
    $this->assertCount(1, $crawler->filter('img[src="https://picsum.photos/1200/600/"][alt="Alternative text for paragraph image"][title="Example"]'));
    $this->assertCount(1, $crawler->filter('div.bcl-featured-media > h2.mb-4'));
    $this->assertCount(1, $crawler->filter('div.bcl-featured-media > div.row'));
    $this->assertCount(1, $crawler->filter('div.bcl-featured-media > div.row > div.col-12.col-md-6.order-md-1'));
    $this->assertCount(1, $crawler->filter('div.bcl-featured-media > div.row > div.col-12.col-md-6.order-md-1 > figure'));
    $this->assertCount(1, $crawler->filter('div.bcl-featured-media > div.row > div.col-12.col-md-6.order-md-2'));
    $this->assertCount(1, $crawler->filter('figure > img.img-fluid'));
    $this->assertCount(1, $crawler->filter('figure > figcaption.bg-light.p-3'));
    $this->assertCount(1, $crawler->filter('h2.mb-4'));
    $this->assertCount(0, $crawler->filter('h5'));
    $this->assertCount(0, $crawler->filter('a'));
    $this->assertCount(0, $crawler->filter('.md-6'));
    $this->assertElementText('Title', 'h2', $crawler);
    $this->assertElementText('Lorem ipsum dolor sit amet.', 'div.col-12.col-md-6.order-md-2', $crawler);
    $this->assertElementText('Media description text goes here.', 'figcaption', $crawler);
  }

  /**
   * Covers 'only with iframe' case.
   */
  protected function coverIframeNotWrapper(): void {
    $crawler = new Crawler($this->getPatternHtml(FALSE, NULL, TRUE));
    $this->assertCount(1, $crawler->filter('div.bcl-featured-media'));
    $this->assertCount(1, $crawler->filter('div.row'));
    $this->assertCount(1, $crawler->filter('div.col-12.col-md-4'));
    $this->assertCount(1, $crawler->filter('figure'));
    $this->assertCount(1, $crawler->filter('figcaption'));
    $this->assertCount(0, $crawler->filter('img'));
    $this->assertCount(1, $crawler->filter('iframe'));
    $this->assertCount(1, $crawler->filter('div.ratio.ratio-16x9'));
    $this->assertCount(1, $crawler->filter('iframe[src="https://www.youtube.com/watch?v=nWpgO1EPO_Y"]'));
    $this->assertCount(1, $crawler->filter('div.bcl-featured-media > div.row'));
    $this->assertCount(1, $crawler->filter('div.bcl-featured-media > div.row > div.col-12.col-md-4'));
    $this->assertCount(1, $crawler->filter('div.bcl-featured-media > div.row > div.col-12.col-md-4 > figure'));
    $this->assertCount(1, $crawler->filter('figure > div.ratio.ratio-16x9'));
    $this->assertCount(1, $crawler->filter('figure > figcaption.bg-light.p-3'));
    $this->assertCount(0, $crawler->filter('h2'));
    $this->assertCount(0, $crawler->filter('h5'));
    $this->assertCount(0, $crawler->filter('a'));
    $this->assertCount(0, $crawler->filter('.md-6,.order-md-1,.order-md-2'));
    $this->assertElementText('Media description text goes here.', 'figcaption', $crawler);
  }

  /**
   * Covers 'with iframe, text aligned left, default ratio' case.
   */
  protected function coverIframeWithTextAlignedLeft(): void {
    $crawler = new Crawler($this->getPatternHtml(TRUE, 'left', TRUE));
    $this->assertCount(1, $crawler->filter('div.bcl-featured-media'));
    $this->assertCount(1, $crawler->filter('div.row'));
    $this->assertCount(1, $crawler->filter('div.col-12.col-md-6.order-md-1'));
    $this->assertCount(1, $crawler->filter('div.col-12.col-md-6.order-md-2'));
    $this->assertCount(1, $crawler->filter('figure'));
    $this->assertCount(1, $crawler->filter('figcaption'));
    $this->assertCount(0, $crawler->filter('img'));
    $this->assertCount(1, $crawler->filter('iframe'));
    $this->assertCount(1, $crawler->filter('div.ratio.ratio-16x9'));
    $this->assertCount(1, $crawler->filter('iframe[src="https://www.youtube.com/watch?v=nWpgO1EPO_Y"]'));
    $this->assertCount(1, $crawler->filter('div.bcl-featured-media > div.row'));
    $this->assertCount(1, $crawler->filter('div.bcl-featured-media > div.row > div.col-12.col-md-6.order-md-1'));
    $this->assertCount(1, $crawler->filter('div.bcl-featured-media > div.row > div.col-12.col-md-6.order-md-2'));
    $this->assertCount(1, $crawler->filter('div.bcl-featured-media > div.row > div.col-12.col-md-6.order-md-2 > figure'));
    $this->assertCount(1, $crawler->filter('figure > div.ratio.ratio-16x9'));
    $this->assertCount(1, $crawler->filter('figure > figcaption.bg-light.p-3'));
    $this->assertCount(1, $crawler->filter('h2.mb-4'));
    $this->assertCount(0, $crawler->filter('h5'));
    $this->assertCount(0, $crawler->filter('a'));
    $this->assertCount(0, $crawler->filter('.md-6'));
    $this->assertElementText('Title', 'h2', $crawler);
    $this->assertElementText('Lorem ipsum dolor sit amet.', 'div.col-12.col-md-6.order-md-1', $crawler);
    $this->assertElementText('Media description text goes here.', 'figcaption', $crawler);
  }

  /**
   * Covers 'with iframe, text aligned right, with link and ratio 21x9' case.
   */
  protected function coverIframeWithTextAlignedRightWithLinkRatio21x9(): void {
    $crawler = new Crawler($this->getPatternHtml(TRUE, 'right', TRUE, TRUE, '21x9'));
    $this->assertCount(1, $crawler->filter('div.bcl-featured-media'));
    $this->assertCount(1, $crawler->filter('div.row'));
    $this->assertCount(1, $crawler->filter('div.col-12.col-md-6.order-md-1'));
    $this->assertCount(1, $crawler->filter('div.col-12.col-md-6.order-md-2'));
    $this->assertCount(1, $crawler->filter('figure'));
    $this->assertCount(1, $crawler->filter('figcaption'));
    $this->assertCount(0, $crawler->filter('img'));
    $this->assertCount(1, $crawler->filter('iframe'));
    $this->assertCount(1, $crawler->filter('div.ratio.ratio-21x9'));
    $this->assertCount(1, $crawler->filter('iframe[src="https://www.youtube.com/watch?v=nWpgO1EPO_Y"]'));
    $this->assertCount(1, $crawler->filter('div.bcl-featured-media > div.row'));
    $this->assertCount(1, $crawler->filter('div.bcl-featured-media > div.row > div.col-12.col-md-6.order-md-1'));
    $this->assertCount(1, $crawler->filter('div.bcl-featured-media > div.row > div.col-12.col-md-6.order-md-1 > figure'));
    $this->assertCount(1, $crawler->filter('div.bcl-featured-media > div.row > div.col-12.col-md-6.order-md-2'));
    $this->assertCount(1, $crawler->filter('div.bcl-featured-media > div.row > div.col-12.col-md-6.order-md-2 a'));
    $this->assertCount(1, $crawler->filter('figure > div.ratio.ratio-21x9'));
    $this->assertCount(1, $crawler->filter('figure > figcaption.bg-light.p-3'));
    $this->assertCount(1, $crawler->filter('h2.mb-4'));
    $this->assertCount(0, $crawler->filter('h5'));
    $this->assertCount(1, $crawler->filter('a'));
    $this->assertCount(1, $crawler->filter('a[href="#example"]'));
    $this->assertCount(0, $crawler->filter('.md-6'));
    $this->assertElementText('Title', 'h2', $crawler);
    $this->assertElementText('Media description text goes here.', 'figcaption', $crawler);
    $this->assertElementTextContains('Lorem ipsum dolor sit amet.', 'div.col-12.col-md-6.order-md-2', $crawler);
    $this->assertElementTextContains('View all', 'div.col-12.col-md-6.order-md-2 a', $crawler);
  }

  /**
   * Asserts the text of an element.
   *
   * @param string $expected
   *   The expected value.
   * @param string $selector
   *   The CSS selector to find the element.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertElementText(string $expected, string $selector, Crawler $crawler): void {
    $element = $crawler->filter($selector);
    $this->assertNotEmpty($element, sprintf('No elements found with selector "%s.', $selector));
    $this->assertSame($expected, trim($element->html()), sprintf('Text in element with selector "%s" is not "%s".', $selector, $expected));
  }

  /**
   * Asserts the text of an element contains string.
   *
   * @param string $string
   *   The string to check.
   * @param string $selector
   *   The CSS selector to find the element.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertElementTextContains(string $string, string $selector, Crawler $crawler): void {
    $element = $crawler->filter($selector);
    $this->assertNotEmpty($element, sprintf('No elements found with selector "%s.', $selector));
    $this->assertStringContainsString($string, $element->html(), sprintf('Text in element with selector "%s" does not contain "%s".', $selector, $string));
  }

  /**
   * Get featured media pattern html for test cases.
   *
   * @param bool $with_text
   *   Whether the pattern has text.
   * @param string|null $text_position
   *   Position of text (left|right).
   * @param bool $with_iframe
   *   Whether the pattern has iframe media instead of image.
   * @param bool $with_link
   *   Whether the pattern has a link.
   * @param string|null $ratio
   *   Custom ratio for the iframe media.
   *
   * @return string
   *   The rendered pattern html string.
   */
  protected function getPatternHtml(bool $with_text = FALSE, ?string $text_position = 'left', bool $with_iframe = FALSE, bool $with_link = FALSE, ?string $ratio = NULL): string {
    $build = [
      '#type' => 'pattern',
      '#id' => 'featured_media',
      '#fields' => [
        'caption' => 'Media description text goes here.',
      ],
    ];

    if ($with_text) {
      $build['#fields']['title'] = 'Title';
      $build['#fields']['text'] = 'Lorem ipsum dolor sit amet.';
      $build['#fields']['text_position'] = $text_position;
    }

    if ($with_iframe) {
      $build['#fields']['embedded_media'] = [
        '#type' => 'html_tag',
        '#tag' => 'iframe',
        '#attributes' => [
          'src' => 'https://www.youtube.com/watch?v=nWpgO1EPO_Y',
        ],
      ];
    }
    else {
      $build['#fields']['image'] = [
        'src' => 'https://picsum.photos/1200/600/',
        'alt' => 'Alternative text for paragraph image',
        'name' => 'Example',
      ];
    }

    if ($with_link) {
      $build['#fields']['link'] = [
        'label' => 'View all',
        'path' => '#example',
      ];
    }

    if ($ratio) {
      $build['#fields']['ratio'] = $ratio;
    }

    return (string) $this->container->get('renderer')->renderRoot($build);
  }

}
