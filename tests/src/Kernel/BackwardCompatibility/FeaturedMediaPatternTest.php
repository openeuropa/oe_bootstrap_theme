<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel\BackwardCompatibility;

use Drupal\Tests\oe_bootstrap_theme\Kernel\AbstractKernelTestBase;
use Drupal\Tests\oe_bootstrap_theme\Traits\BackwardCompatibilityTrait;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests backward compatibility for the featured media pattern.
 */
class FeaturedMediaPatternTest extends AbstractKernelTestBase {

  use BackwardCompatibilityTrait;

  /**
   * Tests subtitle rendering with and without backward compatibility setting.
   */
  public function testBackwardCompatibility(): void {
    $build = [
      '#type' => 'pattern',
      '#id' => 'featured_media',
      '#fields' => [
        'title' => 'Test Title',
        'subtitle' => 'Test Subtitle',
        'text' => 'Some text content.',
        'embedded_media' => '',
        'image' => [
          'src' => 'https://example.org/image.jpg',
          'alt' => 'Example image alt',
        ],
        'caption' => 'This is a caption.',
        'ratio' => '16x9',
        'subtitle_tag' => 'h6',
      ],
    ];

    // Test with backward compatibility setting enabled (h5), but explicit tag.
    $this->setBackwardCompatibilitySetting('featured_media_subtitle_tag_h5', TRUE);
    $html = $this->renderBuild($build);
    $crawler = new Crawler($html);
    $this->assertCount(1, $crawler->filter('h5.text-secondary'));
    $this->assertCount(0, $crawler->filter('h3.text-secondary'));
    $this->assertEquals('Test Subtitle', $crawler->filter('h5.text-secondary')->text());

    // Test with backward compatibility setting disabled (h3), but explicit tag.
    $this->setBackwardCompatibilitySetting('featured_media_subtitle_tag_h5', FALSE);
    $html = $this->renderBuild($build);
    $crawler = new Crawler($html);
    $this->assertCount(0, $crawler->filter('h5.text-secondary'));
    $this->assertCount(1, $crawler->filter('h6.text-secondary'));
  }

  /**
   * Renders the build array and returns the HTML output.
   */
  protected function renderBuild(array $build): string {
    drupal_static_reset();
    \Drupal::configFactory()->clearStaticCache();

    return (string) $this->container->get('renderer')->renderRoot($build);
  }

}
