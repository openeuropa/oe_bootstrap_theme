<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel\BackwardCompatibility;

use Drupal\Tests\oe_bootstrap_theme\Kernel\AbstractKernelTestBase;
use Drupal\Tests\oe_bootstrap_theme\PatternAssertion\CardPatternAssert;
use Drupal\Tests\oe_bootstrap_theme\Traits\BackwardCompatibilityTrait;
use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the backward compatibility settings for the card pattern.
 */
class CardPatternTest extends AbstractKernelTestBase {

  use BackwardCompatibilityTrait;

  /**
   * Tests the backward compatibility settings.
   */
  public function testBackwardCompatibility(): void {
    $build = [
      '#type' => 'pattern',
      '#id' => 'card',
      '#variant' => 'search',
      '#fields' => [
        'title' => 'Test title',
        'text' => 'This is a text example.',
        'image' => [
          'src' => 'https://example.org/image.jpg',
          'alt' => 'Fake image alt',
        ],
        'content' => 'This is the test content.',
      ],
    ];
    $expected = [
      'title' => 'Test title',
      'description' => 'This is a text example.',
      'image' => [
        'src' => 'https://example.org/image.jpg',
        'alt' => 'Fake image alt',
      ],
      'content' => ['This is the test content.'],
    ];

    $html = $this->renderBuild($build);
    $crawler = new Crawler($html);
    // Test the markup when the BC settings are set to FALSE (fresh install).
    $this->assertCount(1, $crawler->filter('.row .bcl-card-start-col.bcl-size-small img.card-img-top'));
    $this->assertCount(0, $crawler->filter('.row .bcl-card-start-col.bcl-size-small img.d-none'));
    $this->assertCount(0, $crawler->filter('.row .bcl-card-start-col.bcl-size-small img.d-md-block'));
    $this->assertCount(1, $crawler->filter('.row .col-12.col-md .card-body'));
    $this->assertCount(0, $crawler->filter('.row .col-md-3'));
    $this->assertCount(0, $crawler->filter('.row .mw-listing-img'));
    $this->assertCount(0, $crawler->filter('.row .col-md-9'));
    $this->assertCount(0, $crawler->filter('.row .col-lg-10'));
    (new CardPatternAssert())->assertPattern($expected, $html);

    // Test that the card pattern fails correctly when TRUE is used for
    // a setting set to FALSE.
    try {
      (new CardPatternAssert(TRUE))->assertPattern($expected, $html);
      $this->fail('The card pattern assert is not covering the "card_search_use_grid_classes" setting.');
    }
    catch (ExpectationFailedException $exception) {
      // The card pattern assert is covering the scenario.
    }
    try {
      (new CardPatternAssert(NULL, TRUE))->assertPattern($expected, $html);
      $this->fail('The card pattern assert is not covering the "card_search_image_hide_on_mobile" setting.');
    }
    catch (ExpectationFailedException $exception) {
      // The card pattern assert is covering the scenario.
    }

    // Test the markup for the "card_search_use_grid_classes" BC setting.
    $this->setBackwardCompatibilitySetting('card_search_use_grid_classes', TRUE);
    $html = $this->renderBuild($build);
    $crawler = new Crawler($html);
    $this->assertCount(1, $crawler->filter('.row .col-md-3.mw-listing-img.mb-md-0.mb-3 img.card-img-top'));
    $this->assertCount(0, $crawler->filter('.row .bcl-card-start-col img'));
    $this->assertCount(0, $crawler->filter('.row .bcl-card-start-col.bcl-size-small img'));
    $this->assertCount(1, $crawler->filter('.row .col-md-9.col-lg-10 .card-body'));
    $this->assertCount(0, $crawler->filter('.row .col-12'));
    $this->assertCount(0, $crawler->filter('.row .col-md'));
    (new CardPatternAssert())->assertPattern($expected, $html);

    try {
      (new CardPatternAssert(FALSE))->assertPattern($expected, $html);
      $this->fail('The card pattern assert is not covering the "card_search_use_grid_classes" setting.');
    }
    catch (ExpectationFailedException $exception) {
      // The card pattern assert is covering the scenario.
    }

    // Test the markup for "card_search_image_hide_on_mobile" BC setting.
    // Image presence is covered in the previous lines.
    $this->assertCount(0, $crawler->filter('.row .col-md-3.mw-listing-img img.d-none'));
    $this->assertCount(0, $crawler->filter('.row .col-md-3.mw-listing-img img.d-md-block'));

    $this->setBackwardCompatibilitySetting('card_search_image_hide_on_mobile', TRUE);
    $html = $this->renderBuild($build);
    $crawler = new Crawler($html);
    $this->assertCount(1, $crawler->filter('.row .col-md-3.mw-listing-img img'));
    $this->assertCount(1, $crawler->filter('.row .col-md-3.mw-listing-img img.d-none.d-md-block'));
    (new CardPatternAssert())->assertPattern($expected, $html);

    try {
      (new CardPatternAssert(NULL, FALSE))->assertPattern($expected, $html);
      $this->fail('The card pattern assert is not covering the "card_search_image_hide_on_mobile" setting.');
    }
    catch (ExpectationFailedException $exception) {
      // The card pattern assert is covering the scenario.
    }

    // Test the remaining BC settings combination scenario.
    $this->setBackwardCompatibilitySetting('card_search_use_grid_classes', FALSE);
    $html = $this->renderBuild($build);
    $crawler = new Crawler($html);
    $this->assertCount(1, $crawler->filter('.row .bcl-card-start-col.bcl-size-small img'));
    $this->assertCount(1, $crawler->filter('.row .bcl-card-start-col.bcl-size-small img.d-none.d-md-block'));
    $this->assertCount(0, $crawler->filter('.row .col-md-3'));
    $this->assertCount(0, $crawler->filter('.row .mw-listing-img'));
    (new CardPatternAssert())->assertPattern($expected, $html);

    try {
      (new CardPatternAssert(NULL, FALSE))->assertPattern($expected, $html);
      $this->fail('The card pattern assert is not covering the "card_search_image_hide_on_mobile" setting.');
    }
    catch (ExpectationFailedException $exception) {
      // The card pattern assert is covering the scenario.
    }
  }

  /**
   * Renders a build array with the current theme settings.
   *
   * @param array $build
   *   The build. Not passed by reference to keep the original variable value.
   *
   * @return string
   *   The rendered HTML.
   */
  protected function renderBuild(array $build): string {
    // Theme settings config is cached statically.
    drupal_static_reset();
    \Drupal::configFactory()->clearStaticCache();

    return (string) $this->container->get('renderer')->renderRoot($build);
  }

}
