<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel;

use Drupal\Component\Serialization\Yaml;
use Drupal\Component\Utility\Html;
use Drupal\Core\Render\Markup;
use Drupal\Core\Site\Settings;
use Drupal\Core\Template\Attribute;
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
          $this->assertNotEmpty($element, $type . ": $selector\n$html");
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
   * Checks generated IDs across multiple renders and existing page IDs.
   *
   * @dataProvider generatedIdProvider
   */
  public function testGeneratedIds(string $id, array $fields, array $expected_ids, array $reserved_ids = []): void {
    foreach (['pattern', 'component'] as $type) {
      Html::resetSeenIds();
      foreach ($reserved_ids as $reserved_id) {
        Html::getUniqueId($reserved_id);
      }
      $html = '';
      foreach ($fields as $values) {
        $render = $type === 'pattern'
          ? ['#type' => 'pattern', '#id' => $id, '#fields' => $values]
          : ['#type' => 'component', '#component' => 'oe_bootstrap_theme:' . $id, '#props' => $values];
        $html = (string) $this->container->get('renderer')->renderInIsolation($render);
      }
      $crawler = new Crawler($html);
      foreach ($expected_ids as $expected_id) {
        $this->assertCount(1, $crawler->filter('[id="' . $expected_id . '"]'), $type);
      }
      if ($id === 'inpage_navigation') {
        $this->assertSame($expected_ids[1], $crawler->filter('nav')->attr('aria-labelledby'), $type);
      }
    }
  }

  /**
   * Provides ID sequences which must remain stable when switching to SDC.
   */
  public static function generatedIdProvider(): array {
    $cases = [];
    foreach (['modal', 'offcanvas', 'inpage_navigation'] as $id) {
      $expected = ['bcl-' . str_replace('_', '-', $id)];
      if ($id === 'inpage_navigation') {
        $expected[] = 'bcl-inpage-navigation-dropdown';
      }
      $cases[$id . '_after_custom_id'] = [
        $id,
        [['attributes' => ['id' => 'custom']], []],
        $expected,
      ];
    }
    $cases['inpage_navigation_existing_dropdown_id'] = [
      'inpage_navigation',
      [['attributes' => ['id' => 'custom']]],
      ['custom', 'custom-dropdown--2'],
      ['custom-dropdown'],
    ];
    $cases['inpage_navigation_repeated'] = [
      'inpage_navigation',
      [[], []],
      ['bcl-inpage-navigation--2', 'bcl-inpage-navigation-dropdown--2'],
    ];
    return $cases;
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
    foreach ([
      'badge_link_background' => ['badge', [
        'label' => 'Badge',
        'background' => 'link',
      ], ['count' => ['span.text-bg-link' => 1]],
      ],
      'link_empty_icon_display' => ['link', [
        'label' => 'Link',
        'icon_display' => '',
      ], ['count' => ['a' => 1, 'a.d-flex, a.d-inline-flex' => 0]],
      ],
      'navigation_nested_flags' => ['navigation', [
        'items' => [[
          'label' => 'Parent',
          'navigation' => [
            'pills' => TRUE,
            'nav' => TRUE,
            'vertical' => TRUE,
            'items' => [['label' => 'Child']],
          ],
        ],
        ],
      ], ['count' => ['nav.nav-pills.flex-column' => 1]],
      ],
      'dropdown_null_button' => ['dropdown', [
        'button_label' => 'Toggle',
        'items' => [['label' => 'Button', 'button' => NULL]],
      ], ['count' => ['button.dropdown-item' => 1, 'a.dropdown-item' => 0]],
      ],
      'dropdown_null_divider' => ['dropdown', [
        'button_label' => 'Toggle',
        'items' => [['divider' => NULL]],
      ], ['count' => ['hr.dropdown-divider' => 1]],
      ],
      'list_group_null_button' => ['list_group', [
        'type' => 'actionable',
        'items' => [['label' => 'Button', 'button' => NULL]],
      ], ['count' => ['button.list-group-item' => 1, 'a.list-group-item' => 0]],
      ],
      'navigation_null_button' => ['navigation', [
        'items' => [['label' => 'Button', 'button' => NULL]],
      ], ['count' => ['button.nav-link' => 1, 'a.nav-link' => 0]],
      ],
      'pagination_control_custom_icon_path' => ['pagination', [
        'first' => ['path' => '#first', 'icon' => ['name' => 'check', 'path' => '/custom.svg']],
      ], ['contains' => ['svg' => 'bcl-default-icons.svg#check']],
      ],
      'pagination_item_custom_icon_path' => ['pagination', [
        'items' => [['label' => 'Item', 'path' => '#item', 'icon' => ['name' => 'check', 'path' => '/custom.svg']]],
      ], [
        'contains' => ['svg' => 'bcl-default-icons.svg#check'],
        'count' => ['svg.icon--s' => 1],
      ],
      ],
      'pagination_control_empty_icon_size' => ['pagination', [
        'first' => ['path' => '#first', 'icon' => ['name' => 'check', 'size' => '']],
      ], ['count' => ['svg.icon--s' => 1]],
      ],
      'pagination_control_root_icon_path' => ['pagination', [
        'first' => [
          'path' => '#first',
          'icon_path' => '/root.svg',
          'icon' => ['name' => 'check', 'path' => '/nested.svg'],
        ],
      ], ['contains' => ['svg' => '/root.svg#check']],
      ],
      'pagination_legacy_last_attributes' => ['pagination', [
        'first' => [
          'path' => '#first',
          'attributes' => new Attribute(['class' => ['first-control']]),
        ],
        'last' => ['path' => '#last', 'attributes' => ['class' => ['last-control']]],
      ], [
        'count' => [
          'a[href="#last"].first-control[aria-label="Last"]' => 1,
          'a.last-control' => 0,
        ],
      ],
      ],
      'description_list_null_icon_defaults' => ['description_list', [
        'items' => [[
          'term' => [['label' => 'Term', 'icon' => ['name' => 'check', 'path' => NULL, 'size' => NULL]]],
          'definition' => 'Definition',
        ],
        ],
      ], [
        'count' => ['svg.icon--s' => 1],
        'contains' => ['svg' => 'xlink:href="#check"'],
      ],
      ],
      'banner_cta_legacy_variant' => ['banner', [
        'call_to_action' => ['label' => 'Link', 'path' => '#link', 'variant' => 'danger'],
      ], ['count' => ['a.bcl-banner__link' => 1, 'a.link-danger' => 0]],
      ],
      'banner_cta_icon_without_path' => ['banner', [
        'call_to_action' => ['label' => 'Link', 'path' => '#link', 'icon' => ['name' => 'check']],
      ], ['contains' => ['svg' => 'xlink:href="#check"']],
      ],
      'featured_media_link_legacy_options' => ['featured_media', [
        'text' => 'Text',
        'link' => [
          'label' => 'Link',
          'path' => '#link',
          'disabled' => TRUE,
          'variant' => 'danger',
          'attributes' => ['class' => ['custom-link']],
        ],
      ], ['count' => ['a[href="#link"]' => 1, 'a[aria-disabled], a.link-danger, a.custom-link' => 0]],
      ],
      'featured_media_link_empty_icon_path' => ['featured_media', [
        'link' => ['label' => 'Link', 'path' => '#link', 'icon' => ['name' => 'check', 'path' => '']],
      ], ['contains' => ['a svg' => 'xlink:href="#check"']],
      ],
    ] as $key => [$id, $fields, $assertions]) {
      $cases[$key] = [[
        'render' => ['#type' => 'pattern', '#id' => $id, '#fields' => $fields],
        'assertions' => $assertions,
      ],
      ];
    }
    foreach (['button', 'link'] as $id) {
      foreach (['empty' => '', 'null' => NULL, 'custom' => '/custom.svg'] as $name => $path) {
        $cases[$id . '_icon_path_' . $name] = [[
          'render' => [
            '#type' => 'pattern',
            '#id' => $id,
            '#fields' => ['label' => 'Label', 'icon' => ['name' => 'check', 'path' => $path]],
          ],
          'assertions' => ['contains' => ['svg' => 'xlink:href="' . $path . '#check"']],
        ],
        ];
      }
    }
    foreach ([
      'number' => [123, '123'],
      'decimal' => [1.5, '1.5'],
      'zero' => [0, '0'],
      'false' => [FALSE, ''],
      'true' => [TRUE, '1'],
      'empty' => ['', ''],
      'null' => [NULL, ''],
      'markup' => [Markup::create('A <em>term</em>'), 'A <em>term</em>'],
      'numeric_label' => [[['label' => 123]], '123'],
    ] as $name => [$term, $label]) {
      $cases['description_list_term_' . $name] = [[
        'render' => [
          '#type' => 'pattern',
          '#id' => 'description_list',
          '#fields' => ['items' => [['term' => $term, 'definition' => 'Definition']]],
        ],
        'assertions' => [
          'count' => ['dt' => 1],
          'equals' => ['dt .d-flex > div' => $label],
        ],
      ],
      ];
    }
    return $cases;
  }

}
