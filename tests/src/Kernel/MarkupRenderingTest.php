<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel;

use Drupal\Component\Serialization\Yaml;
use Drupal\Component\Utility\Html;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Site\Settings;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\oe_bootstrap_theme\Helper\HtmlSnapshotFormatter;
use Drupal\Tests\oe_bootstrap_theme\Helper\HtmlSnapshotNormalizer;
use Drupal\Tests\oe_bootstrap_theme\Helper\HtmlSnapshotStabilizer;
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
   * Tests whether attributes are applied to patterns or components.
   */
  public function testPatternAttributes(): void {
    // Some patterns currently don't pass attributes to the BCL template.
    $known_non_attributes_passing_patterns = [
      'dropdown',
      'inpage_navigation',
      'modal',
    ];
    $problems = [];
    foreach (static::$patternList as $pattern_name) {
      // Set minimal required parameters per pattern, so that the output won't
      // be empty and no errors occur.
      $values = match ($pattern_name) {
        // Some patterns are ok with an empty list of items.
        'dropdown',
        'card_layout',
        'description_list',
        'fact_figures',
        'columns' => ['items' => []],
        // Some patterns need at least one item, but it can be empty.
        'accordion',
        'carousel_v2',
        'breadcrumb' => ['items' => [[]]],
        // Some patterns need at least one item, with minimum data.
        'listing',
        'navigation' => ['items' => [['title' => 'Item title']]],
        'gallery' => ['items' => [['media' => 'The media']]],
        // Some patterns have different required parameters.
        'links_block' => ['links' => [[]]],
        'file' => [
          'file' => [
            'url' => 'https://example.com/download.pdf',
            'language_code' => 'en',
            'extension' => 'pdf',
          ],
        ],
        default => [],
      };
      $render = [
        '#type' => 'inline_template',
        '#template' => "{{ pattern('$pattern_name', values) }}",
        '#context' => [
          'values' => [
            ...$values,
            'attributes' => ['class' => ['custom-class-abc']],
          ],
        ],
      ];
      Html::resetSeenIds();
      set_error_handler(static function (int $type, string $message, string $file, int $line) {
        if ($type === E_USER_DEPRECATED || !(error_reporting() & $type)) {
          return;
        }
        throw new \ErrorException($type . ': ' . $message, 0, $type, $file, $line);
      });
      try {
        $html = (string) $this->container->get(RendererInterface::class)->renderRoot($render);
      }
      catch (\ErrorException $e) {
        $problems['error'][$pattern_name] = $e->getSeverity() . ': ' . $e->getMessage();
        continue;
      }
      catch (\Throwable $e) {
        $problems['exception'][$pattern_name] = get_class($e) . ': ' . $e->getMessage();
        continue;
      }
      finally {
        restore_error_handler();
      }
      if (trim($html) === '') {
        $problems['Output is empty'][] = $pattern_name;
        continue;
      }
      $crawler = new Crawler($html);
      $count = $crawler->filter('.custom-class-abc')->count();
      if ($count === 1) {
        if (in_array($pattern_name, $known_non_attributes_passing_patterns)) {
          $problems['Attributes were applied unexpectedly'][] = $pattern_name;
        }
      }
      elseif ($count === 0) {
        if (!in_array($pattern_name, $known_non_attributes_passing_patterns)) {
          $problems["Attributes were not applied."][] = $pattern_name;
        }
      }
      else {
        $problems["Attributes were applied more than once"][$pattern_name] = $count;
      }
    }
    ksort($problems);
    if ($problems) {
      $this->fail(Yaml::encode($problems));
    }
    $this->addToAssertionCount(1);
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
   * Provides test data for testAllCases().
   *
   * @return array<string, array>
   *   Argument combinations for the test method.
   */
  public static function allCasesProvider(): array {
    return [
      'patterns' => ['patterns'],
      'other' => ['other'],
    ];
  }

  /**
   * Tests all components in a single pass.
   *
   * @param 'patterns'|'other' $mode
   *   Pass 'patterns' to test all cases with patterns.
   *   Pass 'other' to test all cases which don't use a pattern.
   *
   * @dataProvider allCasesProvider
   */
  public function testAllCases(string $mode): void {
    $cases = static::markupRenderingProvider();
    $n = count($cases);
    foreach (array_keys($cases) as $i => $case_name) {
      $restore_callbacks = [];
      try {
        $case = $cases[$case_name];
        $render = $case['render'];
        $render = PatternTestDataMassager::massageDataRecursive($render);
        $assertions = $case['assertions'];
        $backward_compatibility = $case['backward_compatibility'] ?? [];
        if (!empty($backward_compatibility)) {
          foreach ($backward_compatibility as $bc_name => $bc_value) {
            $restore_callbacks[] = $this->setBackwardCompatibilitySetting($bc_name, $bc_value);
          }
        }

        // Skip pattern case when in 'other' mode.
        // Skip non-pattern case when not in 'other' mode.
        if ($mode === 'other' xor ($render['#type'] ?? NULL) !== 'pattern') {
          continue;
        }

        Html::resetSeenIds();

        $html = RenderWrapperForm::renderInFormContext($render, $this->container);
        $this->assertMarkupRendering($assertions, $html);

        Html::resetSeenIds();
        $variation_html = RenderWrapperForm::renderInFormContext($render, $this->container);

        $this->assertHtmlSnapshot($case_name, $html, $variation_html);
      }
      catch (\Throwable $e) {
        throw new \Exception("Failure in '$case_name' ($i of $n).", previous: $e);
      }
      finally {
        foreach ($restore_callbacks as $restore_callback) {
          $restore_callback();
        }
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

  /**
   * Asserts or saves a html snapshot.
   *
   * @param string $case_name
   *   The case name, identifying a snapshot file.
   * @param string $html
   *   The html to export.
   * @param string $variation_html
   *   A variation of the html from the same test case, for noise removal.
   *   Any differences between the two pieces of html are assumed to be random
   *   noise, e.g. a dynamic id suffix, or the current date/time.
   */
  protected function assertHtmlSnapshot(string $case_name, string $html, string $variation_html) {
    $stabilized_html = $this->stabilizeHtml($html, $variation_html);
    $formatted_html = (new HtmlSnapshotFormatter())->format($stabilized_html);
    $file = __DIR__ . '/snapshots/markup_rendering/' . $case_name . '.snapshot.html';
    if (getenv('UPDATE_TESTS')) {
      if (!is_dir(dirname($file))) {
        mkdir(dirname($file), recursive: TRUE);
      }
      file_put_contents($file, $formatted_html);
    }
    else {
      $this->assertFileExists($file);
      $expected_formatted_html = file_get_contents($file);
      $this->assertSame($expected_formatted_html, $formatted_html);
    }
  }

  /**
   * Stabilizes and normalizes html.
   *
   * @param string $html
   *   Original html.
   * @param string $variation_html
   *   Alternative version of the same html, which differs in "noisy" parts.
   *
   * @return string
   *   Stabilized html.
   */
  protected function stabilizeHtml(string $html, string $variation_html): string {
    $normalized_html = $this->normalizeHtml($html);
    $normalized_variation_html = $this->normalizeHtml($variation_html);
    $stabilized_html = (new HtmlSnapshotStabilizer())->stabilize($normalized_html, $normalized_variation_html);
    return $stabilized_html;
  }

  /**
   * Normalizes html between pattern and component.
   *
   * @param string $html
   *   The html to normalize.
   *
   * @return string
   *   The normalized html.
   */
  protected function normalizeHtml(string $html): string {
    $relevant_html = RenderWrapperForm::extractRenderedFormContent($html);
    $normalized_html = (new HtmlSnapshotNormalizer())
      ->withElementCallback(function (\DOMElement $element): void {
        // Remove attributes where pattern and component are different.
        $element->removeAttribute('data-component-id');
        $element->removeAttribute('data-component-variant');
        $element->removeAttribute('data-drupal-selector');
        // Remove a class which is not added in Drupal 10.
        if ($element->tagName === 'textarea') {
          $element->setAttribute('class', implode(' ', array_diff(
            explode(' ', $element->getAttribute('class')),
            ['resize-vertical'],
          )));
        }
      })
      ->normalize($relevant_html);
    return $normalized_html;
  }

}
