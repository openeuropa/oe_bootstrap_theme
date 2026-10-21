<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel;

use Drupal\Component\Serialization\Yaml;
use Drupal\Component\Utility\Html;
use Drupal\Core\Render\Element;
use Drupal\Core\Site\Settings;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Theme\ComponentPluginManager;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\oe_bootstrap_theme\Kernel\fixtures\PatternTestDataMassager;
use Drupal\Tests\oe_bootstrap_theme\Traits\BackwardCompatibilityTrait;
use Drupal\ui_patterns\UiPatternsManagerInterface;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests components and patterns markup.
 *
 * This kernel test is heavily inspired by OpenEuropa Theme (oe_theme) similar
 * test: \Drupal\Tests\oe_theme\Kernel\RenderingTest.
 *
 * @see https://github.com/openeuropa/oe_theme/blob/HEAD/tests/Kernel/RenderingTest.php
 *
 * @group oe_bootstrap_theme
 */
class MarkupRenderingTest extends KernelTestBase {

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
   * List of patterns to be tested.
   *
   * @var string[]
   */
  private static $patternList = [
    'accordion',
    'alert',
    'badge',
    'banner',
    'blockquote',
    'breadcrumb',
    'button',
    'card',
    'card_v2',
    'card_layout',
    'carousel',
    'columns',
    'content_banner',
    'date_block',
    'description_list',
    'dropdown',
    'fact_figures',
    'featured_media',
    'file',
    'icon',
    'gallery',
    'inpage_navigation',
    'link',
    'links_block',
    'list_group',
    'listing',
    'modal',
    'navigation',
    'navbar',
    'offcanvas',
    'pagination',
    'pagination_v2',
    'progress',
    'section',
    'timeline',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Replicate 'file_scan_ignore_directories' from settings.php.
    $settings = Settings::getAll();
    $settings['file_scan_ignore_directories'] = [
      'node_modules',
      'bower_components',
      'vendor',
      'build',
    ];
    new Settings($settings);

    $this->container->get('theme_installer')->install(['oe_bootstrap_theme']);
    $this->config('system.theme')->set('default', 'oe_bootstrap_theme')->save();
    $this->container->set('theme.registry', NULL);

    // Prevent NotFoundHttpException in PathProcessorFront.
    $system_site_config = \Drupal::configFactory()->getEditable('system.site');
    $system_site_config->set('page.front', '/')->save();
  }

  /**
   * Tests rendering of elements.
   *
   * @param array $render
   *   A render array.
   * @param array $assertions
   *   Test assertion expectations.
   * @param array $backward_compatibility
   *   A list of backward compatibility settings and their values.
   *
   * @dataProvider markupRenderingProvider
   */
  public function testMarkupRendering(array $render, array $assertions, array $backward_compatibility = []): void {
    $render = PatternTestDataMassager::massageDataRecursive($render);
    if (!empty($backward_compatibility)) {
      foreach ($backward_compatibility as $name => $value) {
        $this->setBackwardCompatibilitySetting($name, $value);
      }
    }

    $html = RenderWrapperForm::renderInFormContext($render, $this->container);
    $this->assertMarkupRendering($assertions, $html);
  }

  /**
   * Tests all components in a single pass.
   *
   * @param 'patterns'|'components'|'other' $mode
   *   Pass 'patterns' to test all test cases with patterns.
   *   Pass 'components' to test all pattern cases, but with the pattern
   *   converted to a component.
   *   Pass 'other' to test all cases which don't use a pattern.
   * @param bool $use_objects
   *   TRUE to use objects like Attribute, ImageValueObject etc.
   *   FALSE to use string or array representations instead.
   * @param bool $fill_nulls
   *   TRUE to fill all remaining optional parameters with NULL.
   *   FALSE to leave them empty (not passing them).
   */
  #[TestWith(['patterns'], 'patterns, objects')]
  #[TestWith(['components'], 'components, objects')]
  #[TestWith(['patterns', FALSE], 'patterns, raw values')]
  #[TestWith(['components', FALSE], 'components, raw values')]
  #[TestWith(['patterns', 'fill_nulls' => TRUE], 'patterns, fill nulls')]
  #[TestWith(['components', 'fill_nulls' => TRUE], 'components, fill nulls')]
  #[TestWith(['other'], 'other')]
  public function testOnePass(string $mode, bool $use_objects = TRUE, bool $fill_nulls = FALSE): void {
    $cases = static::markupRenderingProvider();
    $n = count($cases);
    foreach (array_keys($cases) as $i => $case_name) {
      $case = $cases[$case_name];
      $render = $case['render'];
      $render = PatternTestDataMassager::massageDataRecursive($render, $use_objects);
      $assertions = $case['assertions'];
      $backward_compatibility = $case['backward_compatibility'] ?? [];
      $restore_callbacks = [];
      if (!empty($backward_compatibility)) {
        if ($mode === 'components') {
          // No support for BC with components.
          continue;
        }
        foreach ($backward_compatibility as $bc_name => $bc_value) {
          $restore_callbacks[] = $this->setBackwardCompatibilitySetting($bc_name, $bc_value);
        }
      }

      if ($mode === 'other' xor ($render['#type'] ?? NULL) !== 'pattern') {
        continue;
      }

      if ($mode === 'components') {
        if (($render['#type'] ?? NULL) !== 'pattern') {
          continue;
        }
        $render = $this->replacePatternsWithComponents($render, $use_objects);
      }

      if ($fill_nulls) {
        $render = $this->fillNulls($render);
      }

      Html::resetSeenIds();

      $html = NULL;
      try {
        $html = RenderWrapperForm::renderInFormContext($render, $this->container);
        $this->assertMarkupRendering($assertions, $html);
      }
      catch (\Throwable $e) {
        file_put_contents(
          $report_file = __DIR__ . '/test.html',
          implode("\n", [
            $case_name,
            date('Y-m-d H:i:s'),
            implode("\n<form", explode('<form', $e->getMessage(), 2)),
            ...match (TRUE) {
              str_contains($e->getMessage(), '<form'),
              !isset($html) => [],
              default => [implode("\n<form", explode('<form', $html, 2))],
            },
          ]),
        );
        throw new \Exception("Failure in '$case_name' ($i of $n).", previous: $e);
      }

      foreach ($restore_callbacks as $restore_callback) {
        $restore_callback();
      }
    }
  }

  /**
   * Replaces patterns with components in a render array.
   *
   * @param array $render
   *   Original render array with pattern.
   * @param bool $use_objects
   *   TRUE, to pass attributes as Attribute object.
   *   FALSE, to pass attributes as array.
   *
   * @return array
   *   Modified render array.
   */
  protected function replacePatternsWithComponents(array $render, bool $use_objects): array {
    if (($render['#type'] ?? NULL) === 'pattern') {
      $render['#type'] = 'component';
      $render['#component'] = 'oe_bootstrap_theme:' . $render['#id'];
      $render['#props'] = [
        ...$render['#fields']['settings'] ?? [],
        ...$render['#settings'] ?? [],
        ...$render['#fields'] ?? [],
      ];
      unset($render['#props']['settings']);
      // Support string attributes in fixtures.
      foreach (preg_grep('#^([a-z]+_|)attributes$#', array_keys($render['#props'])) as $key) {
        $value = $render['#props'][$key] ?? NULL;
        if (is_string($value)) {
          $parse_html = '<div ' . $value . '></div>';
          $attributes = [];
          foreach (Html::load($parse_html)->getElementsByTagName('div') as $div) {
            foreach ($div->attributes as $attr) {
              $attributes[$attr->nodeName] = $attr->nodeValue;
            }
          }
          $attributes_object = new Attribute($attributes);
          $render['#props'][$key] = $use_objects ? $attributes_object : $attributes_object->toArray();
        }
        elseif (is_array($value) && $use_objects) {
          $render['#props'][$key] = new Attribute($value);
        }
        elseif ($value instanceof Attribute && !$use_objects) {
          $render['#props'][$key] = $value->toArray();
        }
      }
    }
    foreach (Element::children($render) as $key) {
      $render[$key] = $this->replacePatternsWithComponents($render[$key], $use_objects);
    }
    return $render;
  }

  /**
   * Populates pattern and component parameters with NULL.
   *
   * @param array $render
   *   A render element which may contain component or pattern elements.
   *
   * @return array
   *   A processed render element.
   */
  protected function fillNulls(array $render): array {
    $fn_fill_nulls = fn (array $array) => array_map(fn () => NULL, $array);
    if (($render['#type'] ?? NULL) === 'pattern') {
      $manager = $this->container->get('plugin.manager.ui_patterns');
      assert($manager instanceof UiPatternsManagerInterface);
      $pattern_definition = $manager->getDefinition($render['#id']);
      assert($pattern_definition !== NULL);
      $render['#fields'] ??= [];
      $render['#settings'] ??= [];
      $render['#fields'] += $fn_fill_nulls($pattern_definition->getFields());
      $render['#settings'] += $fn_fill_nulls($pattern_definition['settings'] ?? []);
    }
    elseif (($render['#type'] ?? NULL) === 'component') {
      $manager = $this->container->get(ComponentPluginManager::class);
      $component_definition = $manager->getDefinition($render['#component']);
      assert(is_array($component_definition));
      $render += [
        '#slots' => [],
        '#props' => [],
      ];
      $render['#slots'] += $fn_fill_nulls($component_definition['slots'] ?? []);
      $render['#props'] += $fn_fill_nulls($component_definition['props']['properties'] ?? []);
    }
    foreach (Element::children($render) as $key) {
      $render[$key] = $this->fillNulls($render[$key]);
    }
    return $render;
  }

  /**
   * Data provider for markup rendering tests.
   *
   * @return array
   *   A set of dump data for testing.
   *
   * @see self::testMarkupRendering()
   * @see tests/fixtures/markup_rendering.yml
   */
  public static function markupRenderingProvider(): array {
    $path = __DIR__ . '/fixtures';
    $patterns_path = "{$path}/markup_rendering_patterns";
    $test_cases = Yaml::decode(file_get_contents("{$path}/markup_rendering.yml"));
    foreach (self::$patternList as $pattern) {
      foreach (Yaml::decode(file_get_contents("{$patterns_path}/{$pattern}.yml")) as $key => $test_case) {
        // Ensure a unique test case key as the two files might share same keys.
        $suffix = 0;
        do {
          $candidate_key = $key . ($suffix ? sprintf(' (%s)', $suffix) : '');
          $suffix++;
        } while (isset($test_cases[$candidate_key]));
        $key = $candidate_key;
        $test_cases[$key] = $test_case;
      }
    }

    return $test_cases;
  }

  /**
   * Runs various assertion on given HTML string via CSS selectors.
   *
   * @param array $assertions
   *   A HTML rendered markup.
   * @param string $html
   *   Test assertions. Associative array with having any of the following keys:
   *   - count: assert how many times the given HTML elements occur.
   *   - equals: assert content of given HTML elements.
   *   - contains: assert content contained in given HTML elements.
   *   - pattern: it will use a pattern assertion class to run the assertions.
   *   For the pattern key syntax, see ::assertPatternMarkup().
   *   Assertions array has to be provided in the following format:
   *
   *   @code
   *   [
   *     'count' => [
   *        "button[data-drupal-selector="button"]" => 1,
   *        "button svg" => 1,
   *     ],
   *     'equals' => [
   *        "button label" => "Button label",
   *     ],
   *     'contains' => [
   *        "button.btn-secondary" => "Button label",
   *     ],
   *   ]
   *   @endcode
   */
  protected function assertMarkupRendering(array $assertions, string $html): void {
    $crawler = new Crawler($html);
    $assertions += array_fill_keys(['count', 'equals', 'contains', 'pattern'], []);

    // Catch any unexpected assertion keys. Compare with an empty array to show
    // only the unexpected keys in the exception message.
    $this->assertEquals([], array_diff(array_keys($assertions), [
      'count',
      'equals',
      'contains',
      'pattern',
    ]));

    if (!empty($assertions['pattern'])) {
      // Pattern assertion key should be the only one present.
      $this->assertEquals(['pattern'], array_keys(array_filter($assertions)));

      $this->assertPatternMarkup($assertions['pattern'], $html);
      return;
    }

    // Assert presence of given strings.
    foreach ($assertions['contains'] as $selector => $string) {
      $element = $crawler->filter($selector);
      $this->assertNotEmpty($element, sprintf('No elements found with selector "%s" in:%s%s', $selector, PHP_EOL, $html));
      $this->assertStringContainsString($string, $element->html(), sprintf('Failed assertion for selector "%s".', $selector));
    }

    // Assert occurrences of given elements.
    foreach ($assertions['count'] as $selector => $expected) {
      $this->assertCount($expected, $crawler->filter($selector), sprintf('Wrong count for selector "%s" in:%s%s', $selector, PHP_EOL, $html));
    }

    // Assert that a given element content equals a given string.
    foreach ($assertions['equals'] as $selector => $expected) {
      $element = $crawler->filter($selector);
      $this->assertNotEmpty($element, sprintf('No elements found with selector "%s" in:%s%s.', $selector, PHP_EOL, $html));
      $this->assertSame($expected, trim($element->html()), sprintf('Failed assertion for selector "%s".', $selector));
    }
  }

  /**
   * Executes the pattern assertion class specified in the test data.
   *
   * @param array $assertions
   *   An associative array with the following keys:
   *   - class: A pattern assertion qualified class name.
   *   - expected: the expectation array to pass to the assertion class.
   * @param string $html
   *   The HTML markup.
   */
  protected function assertPatternMarkup(array $assertions, string $html): void {
    $this->assertEquals([], array_diff(array_keys($assertions), [
      'class',
      'expected',
    ]));

    $crawler = new Crawler($html);
    $pattern_html = $crawler->filter('body > form')->html();

    /** @var \Drupal\Tests\oe_bootstrap_theme\PatternAssertion\PatternAssertInterface $pattern_assert */
    $pattern_assert = new $assertions['class']();
    $pattern_assert->assertPattern($assertions['expected'], $pattern_html);
  }

}
