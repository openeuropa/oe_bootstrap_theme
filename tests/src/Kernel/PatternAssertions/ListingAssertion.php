<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel\PatternAssertions;

use Drupal\file\Entity\File;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class for asserting Listing pattern.
 */
class ListingAssertion {

  /**
   * Assert default variant of Listing is rendering correctly.
   *
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   * @param \Drupal\file\Entity\File $file
   *   Image file added to the list item.
   */
  protected function assertDefaultListingRendering(Crawler $crawler, File $file): void {
    $this->assertCount(6, $crawler->filter('div.listing-item.border-bottom.border-md-0.border-0.card'));
    $this->assertCount(6, $crawler->filter('div.mw-listing-img'));
    $this->assertCount(6, $crawler->filter('div.card-body.p-0.pb-md-0.pb-3'));
    $text_element = $crawler->filter('p.card-text.mb-3');
    $this->assertCount(6, $text_element);
    $this->assertCount(0, $crawler->filter('time'));
    $this->assertImageRendering($crawler, $file);
  }

  /**
   * Assert highlight variant of Listing is rendering correctly.
   *
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   * @param \Drupal\file\Entity\File $file
   *   Image file added to the list item.
   */
  protected function assertHighlightListingRendering(Crawler $crawler, File $file): void {
    $this->assertCount(6, $crawler->filter('div.listing-item--highlight.border-0.bg-lighter.card'));
    $text_element = $crawler->filter('p.card-text.mb-2');
    $this->assertCount(6, $text_element);
    $this->assertCount(0, $crawler->filter('time'));
    $this->assertImageRendering($crawler, $file);
  }

  /**
   * Assert Listing is rendering correctly.
   *
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   * @param int $nid
   *   Node identifier.
   */
  protected function assertListingRendering(Crawler $crawler, int $nid): void {
    $this->assertCount(1, $crawler->filter('div.bcl-listing'));
    $this->assertCount(6, $crawler->filter('div.row-cols-1.g-4 > div.col'));
    $this->assertStringContainsString('Listing item block title', trim($crawler->filter('h4.fw-bold.mb-4')->text()));
    $this->assertCount(6, $crawler->filter('h5.card-title'));
    $link_element = $crawler->filter('a.text-underline-hover');
    $this->assertCount(6, $link_element);
    $this->assertStringContainsString(
      'node/' . $nid,
      $link_element->attr('href')
    );
    $text_element = $crawler->filter('p.card-text');
    $this->assertStringContainsString('Item title 1', trim($crawler->filter('h5.card-title > a.text-underline-hover')->text()));
    $this->assertStringContainsString('Label 1 - 1', trim($crawler->filter('span[class="me-2 badge bg-primary"]')->text()));
    $this->assertStringContainsString('Label 2 - 1', trim($crawler->filter('span[class="badge bg-primary"]')->text()));
    $this->assertStringContainsString(
      'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus ut ex tristique, dignissim sem ac, bibendum est.',
      $text_element->text()
    );
    $this->assertStringContainsString('Article', trim($crawler->filter('span[class="text-muted d-md-inline d-block me-4 mb-2 mb-md-0"]')->text()));
    $this->assertStringContainsString('15 November 2021', trim($crawler->filter('span[class="d-md-inline d-block text-muted mb-2 mb-md-0"]')->text()));
  }

  /**
   * Assert image is rendering.
   *
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   * @param \Drupal\file\Entity\File $file
   *   Image file added to the card.
   */
  protected function assertImageRendering(Crawler $crawler, File $file): void {
    $image_element = $crawler->filter('img.card-img-top.rounded-1.mb-3');
    $this->assertCount(6, $image_element);
    $this->assertStringContainsString(
      file_url_transform_relative(file_create_url($file->getFileUri())),
      $image_element->attr('src')
    );
    $this->assertStringContainsString(
      'Alt for image 1',
      $image_element->attr('alt')
    );
  }

}
