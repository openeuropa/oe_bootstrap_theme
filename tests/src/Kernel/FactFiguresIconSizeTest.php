<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel;

use Drupal\oe_bootstrap_theme\DrupalCompatibility;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the "facts_figures.icon_size" theme setting.
 *
 * The icon size defaults to "3xl" on a fresh theme install, while an existing
 * site not having this setting set, it keeps the previous "l" size for
 * backward compatibility.
 */
class FactFiguresIconSizeTest extends AbstractKernelTestBase {

  /**
   * The number of items rendered per test case.
   */
  protected const ITEM_COUNT = 3;

  /**
   * Tests that the theme setting drives the fact and figures icon size.
   *
   * @param array $build
   *   The render array to test.
   *
   * @dataProvider iconSizeBuildProvider
   */
  public function testIconSize(array $build): void {
    // A fresh theme install defaults the icon size to "3xl" via config/install.
    $crawler = $this->renderBuild($build);
    $this->assertCount(self::ITEM_COUNT, $crawler->filter('svg.bi.icon--3xl'));
    $this->assertCount(0, $crawler->filter('svg.bi.icon--l'));

    // Selecting "l" manually.
    $this->setIconSize('l');
    $crawler = $this->renderBuild($build);
    $this->assertCount(self::ITEM_COUNT, $crawler->filter('svg.bi.icon--l'));
    $this->assertCount(0, $crawler->filter('svg.bi.icon--3xl'));

    // Selecting "3xl" manually.
    $this->setIconSize('3xl');
    $crawler = $this->renderBuild($build);
    $this->assertCount(self::ITEM_COUNT, $crawler->filter('svg.bi.icon--3xl'));
    $this->assertCount(0, $crawler->filter('svg.bi.icon--l'));

    // An existing site has no stored value: the icon size falls back to "l".
    \Drupal::configFactory()->getEditable('oe_bootstrap_theme.settings')
      ->clear('facts_figures')
      ->save();
    $crawler = $this->renderBuild($build);
    $this->assertCount(self::ITEM_COUNT, $crawler->filter('svg.bi.icon--l'));
    $this->assertCount(0, $crawler->filter('svg.bi.icon--3xl'));
  }

  /**
   * Tests that an "icon_size" prop overrides the theme setting.
   */
  public function testIconSizePropOverridesThemeSetting(): void {
    $build = [
      '#type' => 'component',
      '#component' => 'oe_bootstrap_theme:fact_figures',
      '#props' => [
        'icon_size' => 'xl',
        'columns' => 3,
        'title' => 'Fact and figures block',
        'items' => [
          [
            'icon' => 'x-diamond-fill',
            'subtitle' => 'Jira Tickets',
            'title' => '1529 JIRA Ticket',
            'description' => 'Nunc condimentum sapien ut nibh finibus suscipit.',
          ],
        ],
      ],
    ];

    // The prop wins over the default theme setting.
    $crawler = $this->renderBuild($build);
    $this->assertCount(1, $crawler->filter('svg.bi.icon--xl'));
    $this->assertCount(0, $crawler->filter('svg.bi.icon--3xl'));

    // The prop still wins when the theme setting is set too.
    $this->setIconSize('l');
    $crawler = $this->renderBuild($build);
    $this->assertCount(1, $crawler->filter('svg.bi.icon--xl'));
    $this->assertCount(0, $crawler->filter('svg.bi.icon--l'));
  }

  /**
   * Data provider for ::testIconSize().
   *
   * @return array
   *   The test cases, each containing a render array.
   */
  public static function iconSizeBuildProvider(): array {
    $items = [
      [
        'icon' => 'x-diamond-fill',
        'subtitle' => 'Jira Tickets',
        'title' => '1529 JIRA Ticket',
        'description' => 'Nunc condimentum sapien ut nibh finibus suscipit.',
      ],
      [
        'icon' => 'ui-checks',
        'subtitle' => 'Feature tickets',
        'title' => '337 Features',
        'description' => 'Turpis varius congue venenatis, erat dui feugiat.',
      ],
      [
        'icon' => 'wallet-fill',
        'subtitle' => 'Test tickets',
        'title' => '107 Tests',
        'description' => 'Cras vestibulum efficitur mi, quis porta tellus rutrum.',
      ],
    ];

    return [
      'pattern' => [
        [
          '#type' => 'pattern',
          '#id' => 'fact_figures',
          '#fields' => [
            'columns' => 3,
            'title' => 'Fact and figures block',
            'items' => $items,
          ],
        ],
      ],
      'component' => [
        [
          '#type' => 'component',
          '#component' => 'oe_bootstrap_theme:fact_figures',
          '#props' => [
            'columns' => 3,
            'title' => 'Fact and figures block',
            'items' => $items,
          ],
        ],
      ],
    ];
  }

  /**
   * Sets the "facts_figures.icon_size" theme setting.
   *
   * @param string $size
   *   The icon size.
   */
  protected function setIconSize(string $size): void {
    \Drupal::configFactory()->getEditable('oe_bootstrap_theme.settings')
      ->set('facts_figures.icon_size', $size)
      ->save();
  }

  /**
   * Renders the given fact and figures build with the current theme settings.
   *
   * @param array $build
   *   The render array to render.
   *
   * @return \Symfony\Component\DomCrawler\Crawler
   *   A crawler on the rendered markup.
   */
  protected function renderBuild(array $build): Crawler {
    // The theme settings are kept in a memory cache, reset it as a separate
    // save() call just changed the configuration.
    DrupalCompatibility::resetThemeSettingsCache();
    \Drupal::configFactory()->clearStaticCache();

    return new Crawler((string) $this->container->get('renderer')->renderRoot($build));
  }

}
