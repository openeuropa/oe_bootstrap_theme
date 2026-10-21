<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel;

use Drupal\Component\Serialization\Yaml;
use Drupal\Component\Utility\Html;
use Drupal\Core\Site\Settings;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\oe_bootstrap_theme\Kernel\fixtures\PatternTestDataMassager;
use Drupal\Tests\oe_bootstrap_theme\Traits\BackwardCompatibilityTrait;
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
    'carousel_v2',
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
   */
  public function testAllCases(): void {
    $cases = static::markupRenderingProvider();
    $n = count($cases);
    foreach (array_keys($cases) as $i => $case_name) {
      $case = $cases[$case_name];
      $render = $case['render'];
      $render = PatternTestDataMassager::massageDataRecursive($render);
      $assertions = $case['assertions'];
      $backward_compatibility = $case['backward_compatibility'] ?? [];
      $restore_callbacks = [];
      if (!empty($backward_compatibility)) {
        foreach ($backward_compatibility as $bc_name => $bc_value) {
          $restore_callbacks[] = $this->setBackwardCompatibilitySetting($bc_name, $bc_value);
        }
      }

      Html::resetSeenIds();

      try {
        $html = RenderWrapperForm::renderInFormContext($render, $this->container);
        $this->assertMarkupRendering($assertions, $html);
      }
      catch (\Throwable $e) {
        throw new \Exception("Failure in '$case_name' ($i of $n).", previous: $e);
      }

      foreach ($restore_callbacks as $restore_callback) {
        $restore_callback();
      }
    }
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
