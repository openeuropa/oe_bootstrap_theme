<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel\Paragraphs;

use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Tests\oe_bootstrap_theme\Kernel\PatternAssertions\LinksBlockAssertion;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the "Social media follow" paragraphs.
 */
class SocialMediaFollowTest extends LinksBlockAssertion {

  /**
   * Tests the rendering of the paragraph type.
   */
  public function testRendering(): void {
    // Create social media follow paragraph with horizontal variant.
    $paragraph = Paragraph::create([
      'type' => 'oe_social_media_follow',
      'field_oe_title' => 'Social media title',
      'field_oe_social_media_variant' => 'horizontal',
      'oe_bt_links_block_background' => 'gray',
      'field_oe_social_media_links' => $this->getSocialMediaLinks(),
      'field_oe_social_media_see_more' => [
        'title' => 'Other social networks',
        'uri' => 'https://europa.eu/european-union/contact/social-networks_en',
      ],
    ]);
    $paragraph->save();

    // Testing: SocialMediaFollow horizontal gray.
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertBackgroundGray($crawler);
    $this->assertSocialMediaLinksBlockRendering($crawler);
    $this->assertHorizontalLinks($crawler);

    // Verify that links are rendered.
    $this->assertSocialLinks($crawler);

    // Testing: SocialMediaFollow vertical gray.
    $paragraph->get('field_oe_social_media_variant')->setValue('vertical');
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertBackgroundGray($crawler);
    $this->assertSocialMediaLinksBlockRendering($crawler);
    $this->assertVerticalLinks($crawler);

    // Verify that the paragraph contains all the links.
    $this->assertSocialLinks($crawler);

    // Testing: SocialMediaFollow horizontal transparent.
    $paragraph->get('field_oe_social_media_variant')->setValue('horizontal');
    $paragraph->get('oe_bt_links_block_background')->setValue('transparent');
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertBackgroundTransparent($crawler);
    $this->assertSocialMediaLinksBlockRendering($crawler);
    $this->assertHorizontalLinks($crawler);

    // Verify that the paragraph contains all the links.
    $this->assertSocialLinks($crawler);

    // Testing: SocialMediaFollow vertical transparent.
    $paragraph->get('field_oe_social_media_variant')->setValue('vertical');
    $paragraph->get('oe_bt_links_block_background')->setValue('transparent');
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertBackgroundTransparent($crawler);
    $this->assertSocialMediaLinksBlockRendering($crawler);
    $this->assertVerticalLinks($crawler);

    // Verify that the paragraph contains all the links.
    $this->assertSocialLinks($crawler);
  }

}
