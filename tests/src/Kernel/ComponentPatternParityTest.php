<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel;

use Drupal\Component\Serialization\Yaml;
use Drupal\Component\Utility\Html;
use Drupal\Core\Site\Settings;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\oe_bootstrap_theme\Kernel\fixtures\PatternTestDataMassager;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Checks components against the original pattern rendering assertions.
 *
 * @group oe_bootstrap_theme
 */
class ComponentPatternParityTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'filter',
    'oe_bootstrap_theme_helper',
    'system',
    'ui_patterns',
    'ui_patterns_library',
    'ui_patterns_settings',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $settings = Settings::getAll();
    $settings['file_scan_ignore_directories'] = ['node_modules', 'bower_components', 'vendor', 'build'];
    new Settings($settings);
    $this->container->get('theme_installer')->install(['oe_bootstrap_theme']);
    $this->config('system.theme')->set('default', 'oe_bootstrap_theme')->save();
    $this->container->set('theme.registry', NULL);
    $this->config('system.site')->set('page.front', '/')->save();
  }

  /**
   * Checks the same selectors and text against the pattern and component.
   *
   * @dataProvider patternProvider
   */
  public function testPatternParity(array $fixture): void {
    $pattern = $fixture['render'];
    $props = $pattern['#fields'] ?? [];
    $settings = $props['settings'] ?? [];
    unset($props['settings']);
    $component = [
      '#type' => 'component',
      '#component' => 'oe_bootstrap_theme:' . $pattern['#id'],
      '#props' => $props + $settings,
    ];
    if (isset($pattern['#variant'])) {
      $component['#props']['variant'] = $pattern['#variant'];
    }

    foreach ([$pattern, $component] as $render) {
      Html::resetSeenIds();
      $render = PatternTestDataMassager::massageDataRecursive($render);
      $crawler = new Crawler((string) $this->container->get('renderer')->renderInIsolation($render));
      foreach ($fixture['assertions']['count'] ?? [] as $selector => $count) {
        // UI Patterns and SDC add different discovery metadata to the wrapper.
        if (str_contains($selector, 'data-drupal-selector')) {
          continue;
        }
        $this->assertCount($count, $crawler->filter($selector), $selector);
      }
      foreach (['equals', 'contains'] as $kind) {
        foreach ($fixture['assertions'][$kind] ?? [] as $selector => $expected) {
          $element = $crawler->filter($selector);
          $this->assertCount(1, $element, $selector);
          if ($kind === 'equals') {
            $this->assertSame($expected, trim($element->html()), $selector);
          }
          else {
            $this->assertStringContainsString($expected, $element->html(), $selector);
          }
        }
      }
    }
  }

  /**
   * Reuses pattern fixtures without maintaining separate SDC expectations.
   */
  public static function patternProvider(): array {
    $cases = [];
    $components = [
      'accordion', 'banner', 'blockquote', 'button', 'date_block',
      'fact_figures', 'listing', 'modal', 'offcanvas',
    ];
    foreach ($components as $name) {
      foreach (Yaml::decode(file_get_contents(__DIR__ . "/fixtures/markup_rendering_patterns/$name.yml")) as $key => $fixture) {
        // Complex component assertions are covered by the existing suites.
        if (!isset($fixture['assertions']['pattern'])) {
          $cases[$key] = [$fixture];
        }
      }
    }
    foreach ($cases as $key => $case) {
      $name = $case[0]['render']['#id'];
      $field = match ($name) {
        'accordion' => 'open_item',
        'fact_figures' => 'columns',
        default => NULL,
      };
      if ($field !== NULL) {
        $fields = $case[0]['render']['#fields'];
        if (isset($fields[$field])) {
          $fields[$field] = (string) $fields[$field];
        }
        elseif (isset($fields['settings'][$field])) {
          $fields['settings'][$field] = (string) $fields['settings'][$field];
        }
        else {
          continue;
        }
        $case[0]['render']['#fields'] = $fields;
        $cases[$key . '_numeric_string'] = $case;
      }
    }
    $date = $cases['date_block_default'];
    $date[0]['render']['#fields']['date'] = new \DateTimeImmutable('2021-05-26');
    $cases['date_block_datetime'] = $date;

    $gallery = Yaml::decode(file_get_contents(__DIR__ . '/fixtures/markup_rendering_patterns/gallery.yml'))['gallery_default'];
    foreach (['original', 'cover'] as $fit) {
      $gallery['render']['#fields']['settings']['thumbnail_fit'] = $fit;
      $gallery['assertions'] = [
        'count' => [
          '.bcl-gallery' => 1,
          '.bcl-gallery--thumbnail-cover' => (int) ($fit === 'cover'),
        ],
      ];
      $cases['gallery_thumbnail_' . $fit] = [$gallery];
    }
    return $cases;
  }

}
