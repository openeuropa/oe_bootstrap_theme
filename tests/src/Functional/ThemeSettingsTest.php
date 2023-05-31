<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\Functional;

use Drupal\Tests\BrowserTestBase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the theme settings.
 */
class ThemeSettingsTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'oe_bootstrap_theme_helper',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'oe_bootstrap_theme';

  /**
   * Tests the table theme settings.
   */
  public function testTableSettings(): void {
    $user = $this->drupalCreateUser([
      'access administration pages',
      'administer themes',
    ]);
    $this->drupalLogin($user);

    $this->drupalGet('/admin/appearance/settings/oe_bootstrap_theme');
    $assert_session = $this->assertSession();

    $assert_session->checkboxChecked('Style all tables using Bootstrap.');
    $responsive_field = $assert_session->selectExists('Responsive');
    $this->assertEqualsCanonicalizing([
      'Never' => 'Never',
      'always' => 'Always',
      'sm' => 'Small',
      'md' => 'Medium',
      'lg' => 'Large',
      'xl' => 'Extra large',
      'xxl' => 'Extra extra large',
    ], $this->getOptions($responsive_field));
    $this->assertEquals('always', $responsive_field->getValue());
    // The MarkupRenderingTest runs assertions on the default config values
    // already.
    $responsive_field->setValue('');
    $assert_session->buttonExists('Save configuration')->press();
    $this->assertEquals('<table data-striping="1" class="table"><tbody><tr><td>A</td><td>B</td></tr></tbody></table>', $this->renderTestTable());

    $responsive_field->setValue('xxl');
    $assert_session->buttonExists('Save configuration')->press();
    $this->assertEquals('<div class="table-responsive-xxl"><table data-striping="1" class="table"><tbody><tr><td>A</td><td>B</td></tr></tbody></table></div>', $this->renderTestTable());

    $responsive_field->setValue('md');
    $assert_session->buttonExists('Save configuration')->press();
    $this->assertEquals('<div class="table-responsive-md"><table data-striping="1" class="table"><tbody><tr><td>A</td><td>B</td></tr></tbody></table></div>', $this->renderTestTable());

    $assert_session->fieldExists('Style all tables using Bootstrap.')->uncheck();
    $assert_session->buttonExists('Save configuration')->press();
    $this->assertEquals('<table data-striping="1"><tbody><tr><td>A</td><td>B</td></tr></tbody></table>', $this->renderTestTable());

    // Test that when the Bootstrap table setting is off, the rendering can be
    // forced via theme suggestions.
    $this->assertEquals('<table data-striping="1" class="table"><tbody><tr><td>A</td><td>B</td></tr></tbody></table>', $this->renderTestTable('table__bootstrap'));
    $this->assertEquals('<div class="table-responsive-md"><table data-striping="1" class="table"><tbody><tr><td>A</td><td>B</td></tr></tbody></table></div>', $this->renderTestTable('table__bootstrap__responsive'));
  }

  /**
   * Tests the backwards compatibility theme settings.
   */
  public function testBackwardsCompatibilitySettings() {
    $user = $this->drupalCreateUser([
      'access administration pages',
      'administer themes',
    ]);
    $this->drupalLogin($user);

    $this->drupalGet('/admin/appearance/settings/oe_bootstrap_theme');
    $assert_session = $this->assertSession();

    $build = [
      '#type' => 'pattern',
      '#id' => 'card',
      '#variant' => 'search',
      '#fields' => [
        'title' => 'Test title',
        'subtitle' => 'Test subtitle',
        'text' => 'This is a text example.',
        'image' => [
          'src' => 'image_source',
          'alt' => 'image_alt',
        ],
        'content' => '<p>this is the test content</p>',
      ],
    ];

    $crawler = $this->getCrawlerFromBuild($build);
    $node = $crawler->filter('img.d-none.d-md-block');
    $this->assertCount(0, $node);
    $node = $crawler->filter('.col-md-3.col-lg-2.rounded.mw-listing-img');
    $this->assertCount(0, $node);
    $node = $crawler->filter('.col-md-9.col-lg-10');
    $this->assertCount(0, $node);

    // Assert card_search_image_not_visible_on_mobile.
    $checkbox = $assert_session->fieldExists('Card image not visible on mobile');
    $checkbox->check();
    $assert_session->buttonExists('Save configuration')->press();

    $crawler = $this->getCrawlerFromBuild($build);
    $image = $crawler->filter('img.d-none.d-md-block');
    $this->assertCount(1, $image);

    // Assert card_search_use_grid_classes.
    $checkbox = $assert_session->fieldExists('Card to use grid classes');
    $checkbox->check();
    $assert_session->buttonExists('Save configuration')->press();

    $crawler = $this->getCrawlerFromBuild($build);
    $node = $crawler->filter('.col-md-3.col-lg-2.rounded.mw-listing-img');
    $this->assertCount(1, $node);
    $node = $crawler->filter('.col-md-9.col-lg-10');
    $this->assertCount(1, $node);
  }

  /**
   * Renders a table for test purposes.
   *
   * @return string
   *   The table normalised markup.
   */
  protected function renderTestTable(string $theme = 'table'): string {
    $build = [
      '#theme' => $theme,
      '#rows' => [
        ['A', 'B'],
      ],
    ];

    // Theme settings config is cached statically.
    drupal_static_reset();
    \Drupal::configFactory()->clearStaticCache();

    $html = (string) $this->container->get('renderer')->renderRoot($build);
    return trim(preg_replace('/>\s+</', '><', $html));
  }

  /**
   * Gets a crawled build.
   *
   * @param array $build
   *   The build.
   *
   * @return \Symfony\Component\DomCrawler\Crawler
   *   The crawler.
   */
  protected function getCrawlerFromBuild(array $build): Crawler {
    // Theme settings config is cached statically.
    drupal_static_reset();
    \Drupal::configFactory()->clearStaticCache();

    $html = (string) $this->container->get('renderer')->renderRoot($build);

    return new Crawler($html);
  }

}
