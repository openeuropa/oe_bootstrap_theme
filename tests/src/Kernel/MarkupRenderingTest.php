<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Serialization\Yaml;
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
class MarkupRenderingTest extends KernelTestBase implements FormInterface {

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
   * @param array $render_array
   *   A render array.
   * @param array $expectations
   *   Test assertion expectations.
   * @param array $bc_settings
   *   A list of backward compatibility settings and their values.
   *
   * @dataProvider markupRenderingProvider
   */
  public function testMarkupRendering(array $render_array, array $expectations, array $bc_settings = []): void {
    if (!empty($bc_settings)) {
      foreach ($bc_settings as $name => $value) {
        $this->setBackwardCompatibilitySetting($name, $value);
      }
    }

    // Wrap all the test structure inside a form. This will allow proper
    // processing of form elements and invocation of form alter hooks. Even if
    // the elements being tested are not form related, the form can host them
    // without causing any issues.
    $form_state = new FormState();
    $form_state->addBuildInfo('args', [$render_array]);
    $form_state->setProgrammed();

    $form = $this->container->get('form_builder')->buildForm($this, $form_state);
    $this->assertMarkupRendering($expectations, (string) $this->container->get('renderer')->renderRoot($form));
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
  public function markupRenderingProvider(): array {
    $path = __DIR__ . '/fixtures';
    $patterns_path = "{$path}/markup_rendering_patterns";
    $test_cases = Yaml::decode(file_get_contents("{$path}/markup_rendering.yml"));
    foreach (self::$patternList as $pattern) {
      foreach (Yaml::decode(file_get_contents("{$patterns_path}/{$pattern}.yml")) as $key => $test_case) {
        // Ensure unique test case key as the two files might share same keys.
        $suffix = 0;
        do {
          $candidate_key = $key . ($suffix ? sprintf(' (%s)', $suffix) : '');
          $suffix++;
        } while (isset($test_cases[$candidate_key]));
        $key = $candidate_key;
        $test_case['render'] = PatternTestDataMassager::massageData($pattern, $test_case['render']);
        $test_case['bc_settings'] = $test_case['bc_settings'] ?? [];
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
   * Builds the form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array|null $render_array
   *   The element render array.
   *
   * @return array
   *   The form render array.
   */
  public function buildForm(array $form, FormStateInterface $form_state, ?array $render_array = NULL): array {
    $form['test'] = $render_array;
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    // Recurse through all the form elements and check if they have a property
    // "#set_validation_error". If they have, set a generic error on the
    // element.
    $add_errors = function (array $element) use (&$add_errors, $form_state): void {
      if (!empty($element['#set_validation_error'])) {
        // When the title is not present for a form element, fallback to its
        // path in the form.
        $label = !empty($element['#title']) ? $element['#title'] : implode('][', $element['#array_parents']);
        $form_state->setError($element, t('Validation error on @label', ['@label' => $label]));
      }

      foreach (Element::children($element) as $key) {
        // Recursively call this closure on all the children elements.
        $add_errors($element[$key]);
      }
    };

    $add_errors($form['test']);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {}

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'oe_bootstrap_theme_markup_rendering_test_form';
  }

}
