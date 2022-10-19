<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\PatternAssertion;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the gallery pattern.
 */
class GalleryPatternAssert extends BasePatternAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions(string $variant): array {
    return [
      'items' => [
        [$this, 'assertItems'],
      ],
      'title' => [
        [$this, 'assertElementText'],
        '.mb-4.bcl-heading',
      ],
      'title_tag' => [
        [$this, 'assertTitleTag'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function assertBaseElements(string $html, string $variant): void {
    $crawler = new Crawler($html);

    $this->assertElementExists('body > .bcl-gallery', $crawler);
    $this->assertElementExists('#modalBcl-gallery.modal', $crawler);

    $items = $crawler->filter('ul.bcl-gallery__grid li')->count();
    if ($items > 5) {
      $view_more_text = "view the full gallery ($items)";
      $this->assertElementText('view less', '.bcl-gallery a.bcl-gallery__collapse span.label-expanded', $crawler);
      $this->assertElementText($view_more_text, '.bcl-gallery a.bcl-gallery__collapse span.label-collapsed', $crawler);
      $this->assertElementText($view_more_text, '.bcl-gallery a.bcl-gallery__mobile-view-more span.label-collapsed', $crawler);

    }
    else {
      $this->assertElementNotExists('.bcl-gallery a.bcl-gallery__collapse', $crawler);
      $this->assertElementNotExists('.bcl-gallery a.bcl-gallery__mobile-view-more', $crawler);
    }

  }

  /**
   * Checks the tag used for the title.
   *
   * @param string $expected
   *   The expected tag.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  public function assertTitleTag(string $expected, Crawler $crawler): void {
    $this->assertElementExists($expected . '.mb-4.bcl-heading', $crawler);
  }

  /**
   * Asserts the gallery items.
   *
   * @param array $expected
   *   The expected items.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  public function assertItems(array $expected, Crawler $crawler): void {
    $expected_visible = array_slice($expected, 0, 5);
    $expected_hidden = array_slice($expected, 5);

    $visible_thumbnails = $crawler->filter('.bcl-gallery > .bcl-gallery__thumbnails > ul.bcl-gallery__grid li');
    self::assertSameSize($expected_visible, $visible_thumbnails);
    $hidden_thumbnails = $crawler->filter('.bcl-gallery__thumbnails > .bcl-gallery__thumbnails-collaspe > ul.bcl-gallery__grid li');
    self::assertSameSize($expected_hidden, $hidden_thumbnails);

    $this->assertGalleryGrid($expected_visible, $visible_thumbnails);
    $this->assertGalleryGrid($expected_hidden, $hidden_thumbnails);

    $expected_carousel = [];
    foreach ($expected as $item) {
      $expected_carousel[] = [
        'image' => $item['media']['rendered'],
        'caption_title' => $item['media']['caption_title'] ?? NULL,
        'caption' => $item['media']['caption_media'] ?? NULL,
      ];
    }

    $carousel = $crawler->filter('.modal .modal-body .carousel');
    self::assertCount(1, $carousel);
    (new CarouselPatternAssert())->assertPattern([
      'items' => $expected_carousel,
    ], $carousel->outerHtml());
  }

  /**
   * Asserts a gallery grid.
   *
   * @param array $expected
   *   The expected items.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The <li> elements.
   */
  protected function assertGalleryGrid(array $expected, Crawler $crawler): void {
    foreach ($expected as $i => $item) {
      $thumbnail = $crawler->eq($i);

      try {
        self::assertStringContainsString($item['thumbnail']['rendered'], $thumbnail->html());
        $this->assertElementText($item['thumbnail']['caption_title'] ?? NULL, '.bcl-gallery__item-caption > h5', $thumbnail);
        $this->assertElementText($item['thumbnail']['caption'] ?? NULL, '.bcl-gallery__item-caption > .bcl-gallery__item-description', $thumbnail);
      }
      catch (\Exception $e) {
        throw new \Exception(sprintf('Failed asserting data for loop item %s.', $i), 0, $e);
      }
    }
  }

}
