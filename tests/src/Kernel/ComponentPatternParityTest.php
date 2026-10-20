<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel;

use Drupal\Component\Serialization\Yaml;
use Drupal\Component\Utility\Html;
use Drupal\Core\Site\Settings;
use Drupal\oe_bootstrap_theme\ValueObject\ImageValueObject;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\oe_bootstrap_theme\Kernel\fixtures\PatternTestDataMassager;
use Drupal\Tests\oe_bootstrap_theme\Traits\BackwardCompatibilityTrait;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Checks components against the original pattern rendering assertions.
 *
 * @group oe_bootstrap_theme
 */
class ComponentPatternParityTest extends KernelTestBase {

  use BackwardCompatibilityTrait;

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
    foreach ($fixture['backward_compatibility'] ?? [] as $name => $value) {
      $this->setBackwardCompatibilitySetting($name, $value);
    }
    $pattern = $fixture['render'];
    $props = $pattern['#fields'] ?? [];
    $settings = ($pattern['#settings'] ?? []) + ($props['settings'] ?? []);
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
      $type = $render['#type'];
      $html = (string) $this->container->get('renderer')->renderInIsolation($render);
      $html = preg_replace('/ data-component-id="[^"]+"/', '', $html);
      $crawler = new Crawler($html);
      if (isset($fixture['assertions']['pattern'])) {
        $assertion = $fixture['assertions']['pattern'];
        (new $assertion['class']())->assertPattern($assertion['expected'], $html);
      }
      foreach ($fixture['assertions']['count'] ?? [] as $selector => $count) {
        // UI Patterns and SDC add different discovery metadata to the wrapper.
        if (str_contains($selector, 'data-drupal-selector')) {
          continue;
        }
        $this->assertCount($count, $crawler->filter($selector), $type . ": $selector\n$html");
      }
      foreach (['equals', 'contains'] as $kind) {
        foreach ($fixture['assertions'][$kind] ?? [] as $selector => $expected) {
          $element = $crawler->filter($selector);
          $this->assertNotEmpty($element, $selector);
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
    foreach (glob(__DIR__ . '/fixtures/markup_rendering_patterns/*.yml') as $path) {
      foreach (Yaml::decode(file_get_contents($path)) as $key => $fixture) {
        if (isset($cases[$key])) {
          $key = basename($path, '.yml') . ':' . $key;
        }
        $cases[$key] = [$fixture];
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
    foreach ([FALSE, TRUE] as $grid) {
      foreach ([FALSE, TRUE] as $hidden) {
        $card = $cases['card_variant_search'];
        $card[0]['backward_compatibility'] = [
          'card_search_use_grid_classes' => $grid,
          'card_search_image_hide_on_mobile' => $hidden,
        ];
        $cases['card_variant_search_legacy_' . (int) $grid . (int) $hidden] = $card;
      }
    }

    $range = $cases['card_with_date_range'];
    $range[0]['assertions']['contains'] = ['time svg' => 'xlink:href="#chevron-right"'];
    $cases['card_with_date_range_icon_path'] = $range;

    $image = $cases['featured_media_image_not_wrapper'];
    $fields = $image[0]['render']['#fields'];
    $image[0]['render']['#fields']['image'] = ImageValueObject::fromArray($fields['image']);
    $cases['featured_media_image_value_object'] = $image;
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
