<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel;

use Drupal\Component\Serialization\Yaml;
use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Render\Markup;
use Drupal\Core\Site\Settings;
use Drupal\Core\Template\Attribute;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\oe_bootstrap_theme\Helper\MarkupExporter;
use Drupal\Tests\oe_bootstrap_theme\Helper\MultiTagYaml;
use Drupal\Tests\oe_bootstrap_theme\Helper\YamlDataComparator;
use Drupal\Tests\oe_bootstrap_theme\Kernel\fixtures\OptionalPropsNullifier;
use Drupal\Tests\oe_bootstrap_theme\Kernel\fixtures\PatternTestDataMassager;
use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Yaml\Tag\TaggedValue;

/**
 * Tests SDC components markup.
 *
 * @group oe_bootstrap_theme
 */
class ComponentRenderingTest extends KernelTestBase implements FormInterface {

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
   * List of SDC components to be tested.
   *
   * @var string[]
   */
  private static $componentList = [
    'accordion',
    'alert',
    'badge',
    'banner',
    'button',
    'blockquote',
    'breadcrumb',
    'card',
    'card_layout',
    'carousel',
    'card_v2',
    'columns',
    'content_banner',
    'date_block',
    'description_list',
    'dropdown',
    'fact_figures',
    'featured_media',
    'file',
    'gallery',
    'icon',
    'inpage_navigation',
    'link',
    'list_group',
    'listing',
    'modal',
    'navbar',
    'navigation',
    'offcanvas',
    'pagination',
    'pagination_v2',
    'links_block',
    'progress',
    'timeline',
    'section',
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
   * Tests rendering of SDC components.
   *
   * @param array $render
   *   A render array.
   * @param array $assertions
   *   Test assertion expectations.
   *
   * @dataProvider componentRenderingProvider
   */
  public function testComponentRendering(array $render, array $assertions, string $name, mixed $backward_compatibility = NULL): void {
    false && set_error_handler(static function (int $type, string $message, string $file, int $line) {
      debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
      if (__FILE__ === $file) {
        $trace = debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        $file = $trace[2]['file'] ?? $file;
        $line = $trace[2]['line'] ?? $line;
      }

      throw new \ErrorException($message, 0, $type, $file, $line);
    });
    $remove = new \stdClass();
    $clean_tree = function ($tree) use (&$clean_tree, $remove) {
      if ($tree instanceof TaggedValue) {
        if (str_contains($tree->getTag(), 'data-component-')) {
          return $remove;
        }
        return new TaggedValue($tree->getTag(), $clean_tree($tree->getValue()));
      }
      if (is_array($tree)) {
        $tree = array_map(fn ($item) => $clean_tree($item), $tree);
        $is_list = array_is_list($tree);
        $tree = array_filter($tree, fn ($v) => $v !== $remove);
        if ($is_list) {
          $tree = array_values($tree);
        }
      }
      return $tree;
    };

    $fixture_render = $render;
    // Internal Url objects need Drupal services, unavailable in the provider.
    $render = PatternTestDataMassager::massageDataRecursive($fixture_render);
    $render = $this->replacePatternsWithComponents($render);
    $exporter = new MarkupExporter();
    $reference_markup = $this->renderInFormContext($render);
    #$this->assertComponentRendering($assertions, $reference_markup);
    $reference_tree = $exporter->export($reference_markup);
    $reference_tree = $clean_tree($reference_tree);
    Html::resetSeenIds();
    $markup = $this->renderInFormContext($render);
    # $this->assertComponentRendering($assertions, $markup);

    # $reference_yaml = $stabilize_and_export_to_yaml($markup);
    $tree = $exporter->export($markup);
    $tree = $clean_tree($tree);
    $comparator = new YamlDataComparator();
    $noisy = $comparator->detectNoise($reference_tree, $tree);

    #mkdir(__DIR__ . '/recordings/markup_rendering_components', recursive: TRUE);
    file_put_contents(__DIR__ . '/recordings/markup_rendering_components/' . $name . '.yml', MultiTagYaml::encode($noisy));
    return;

    $do_test = function (array $render) use ($noisy, $assertions, $exporter, $clean_tree, $name): void {
      Html::resetSeenIds();
      $markup = $this->renderInFormContext($render);
      $tree = $exporter->export($markup);
      $tree = $clean_tree($tree);
      $comparator = new YamlDataComparator();
      $reference = $comparator->align($noisy, $tree);
      mkdir(__DIR__ . '/recordings/markup_rendering_components', recursive: TRUE);
      file_put_contents(__DIR__ . '/recordings/markup_rendering_components/' . $name . '.yml', MultiTagYaml::encode($tree));
      MultiTagYaml::assertSame($reference, $tree);
      $this->assertComponentRendering($assertions, $markup);
    };

    # $metadata = $this->container->get('plugin.manager.sdc')->find($render['#component'])->metadata;
    // Cover plain values in the same pass as explicit NULL props.
    $render = PatternTestDataMassager::massageDataRecursive($fixture_render, FALSE);
    // Before Drupal 11.3, core requires an Attribute object for this prop.
    if (version_compare(\Drupal::VERSION, '11.3.0', '<') && is_array($render['#props']['attributes'] ?? NULL)) {
      $render['#props']['attributes'] = new Attribute($render['#props']['attributes']);
    }
    $render['#props'] = OptionalPropsNullifier::populate($metadata->schema, $render['#props'] ?? []);
    // Cover native variants in this pass where core supports them.
    if (!empty($metadata->variants) && array_key_exists('variant', $render['#props'])) {
      $render['#variant'] = $render['#props']['variant'];
      unset($render['#props']['variant']);
    }
    // Each render represents a separate page with its own generated IDs.
    Html::resetSeenIds();
    $markup = $this->renderInFormContext($render);
    $this->assertComponentRendering($assertions, $markup);
    # $render = PatternTestDataMassager::massageDataRecursive($fixture_render, FALSE);

    $render = $this->replacePatternsWithComponents($render);

    $do_test($render);
  }

  protected function replacePatternsWithComponents(array $render): array {
    if (($render['#type'] ?? NULL) === 'pattern') {
      $render['#type'] = 'component';
      $render['#component'] = 'oe_bootstrap_theme:' . $render['#id'];
      $render['#props'] = [
        ...$render['#fields']['settings'] ?? [],
        ...$render['#settings'] ?? [],
        ...$render['#fields'] ?? [],
      ];
      unset($render['#props']['settings']);
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
          $render['#props'][$key] = new Attribute($attributes);
        }
      }
    }
    foreach (Element::children($render) as $key) {
      $render[$key] = $this->replacePatternsWithComponents($render[$key]);
    }
    return $render;
  }

  /**
   * Renders a render array as part of a form.
   *
   * @param array $render
   *   A render element.
   *
   * @return string
   *   The rendered markup.
   */
  protected function renderInFormContext(array $render): string {
    $form_state = new FormState();
    $separator = '<!-- CUT -->';
    /* @see self::buildForm() */
    $form_state->addBuildInfo('args', [$render, $separator]);
    $form_state->setProgrammed();

    $form = $this->container->get('form_builder')->buildForm($this, $form_state);
    $markup = (string) $this->container->get('renderer')->renderRoot($form);
    // Extract the part of the form that we want. Discard the form wrapper.
    $parts = explode($separator, $markup);
    $this->assertGreaterThanOrEqual(3, count($parts));
    $inner_parts = array_slice($parts, 1, -1);
    return implode($separator, $inner_parts);
  }

  /**
   * Data provider for component rendering tests.
   *
   * @return array
   *   A set of test data.
   */
  public static function componentRenderingProvider(): array {
    $components_path = __DIR__ . '/fixtures/markup_rendering_components';
    $test_cases = [];

    foreach (self::$componentList as $component) {
      foreach (Yaml::decode(file_get_contents("{$components_path}/{$component}.yml")) as $key => $test_case) {
        // Ensure unique test case keys across components.
        $suffix = 0;
        do {
          $candidate_key = $key . ($suffix ? sprintf(' (%s)', $suffix) : '');
          $suffix++;
        } while (isset($test_cases[$candidate_key]));
        static::assertSame(['render', 'assertions'], array_keys($test_case));
        $test_case['name'] = $candidate_key;
        $test_cases[$candidate_key] = $test_case;
      }
    }

    $patterns_path = __DIR__ . '/fixtures/markup_rendering_patterns';
    $pattern_filenames = preg_grep('#^\w+\.yml$#', scandir($patterns_path));
    foreach ($pattern_filenames as $pattern_filename) {
      #$test_name = 'patty.' . substr($pattern_filename, 0, -4);
      $yaml = file_get_contents("$patterns_path/$pattern_filename");
      $cases = Yaml::decode($yaml);
      foreach ($cases as $case_name => $case) {
        // Ensure unique test case keys across components.
        $case_key = 'patty.' . $case_name;
        for ($suffix = 0; isset($test_cases[$case_key]); ++$suffix) {
          $case_key = 'patty.' . $case_name . ' (' . $suffix . ')';
        }
        try {
          static::assertSame(
            ['render', 'assertions', 'backward_compatibility'],
            array_keys($case + ['backward_compatibility' => NULL]),
            $case_key,
          );
        }
        catch (ExpectationFailedException $e) {
          print get_class($e)
            . "\n\n:  " . $e->getComparisonFailure()?->getDiff()
            . "\n\n:  " . $e->getPrevious()?->getMessage()
            . "\n\n:  " . $e->getMessage()
            . "\n\n" . __FILE__ . ':' . __LINE__
            . "\n\n" . (new \Exception)->getTraceAsString()
            . "\n\n";
          throw $e;
        }
        $case['name'] = $case_key;
        $test_cases[$case_key] = $case;
      }
    }

    return $test_cases;
  }

  /**
   * Runs various assertions on given HTML string via CSS selectors.
   *
   * Assertions array has to be provided in the following format:
   *
   * @code
   * [
   *   'count' => [
   *      "button[data-drupal-selector="button"]" => 1,
   *      "button svg" => 1,
   *   ],
   *   'equals' => [
   *      "button label" => "Button label",
   *   ],
   *   'contains' => [
   *      "button.btn-secondary" => "Button label",
   *   ],
   * ]
   * @endcode
   *
   * @param array $assertions
   *   Test assertions.
   * @param string $html
   *   The rendered HTML markup.
   */
  protected function assertComponentRendering(array $assertions, string $html): void {
    $assertions += array_fill_keys(['count', 'equals', 'contains', 'component'], []);

    // Catch any unexpected assertion keys. Compare with an empty array to show
    // only the unexpected keys in the exception message.
    $this->assertEquals([], array_diff(array_keys($assertions), [
      'count',
      'equals',
      'contains',
      'component',
    ]));

    if (!empty($assertions['component'])) {
      // Component assertion key should be the only one present.
      $this->assertEquals(['component'], array_keys(array_filter($assertions)));
      $this->assertComponentMarkup($assertions['component'], $html);
      return;
    }

    $crawler = new Crawler($html);

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
   * Executes the component assertion class specified in the test data.
   *
   * @param array $assertions
   *   An associative array with 'class' and 'expected' keys.
   * @param string $html
   *   The HTML markup.
   */
  protected function assertComponentMarkup(array $assertions, string $html): void {
    $this->assertEquals([], array_diff(array_keys($assertions), [
      'class',
      'expected',
    ]));

    $crawler = new Crawler($html);
    $component_html = $crawler->filter('body')->html();

    /** @var \Drupal\Tests\oe_bootstrap_theme\ComponentAssertion\ComponentAssertInterface $component_assert */
    $component_assert = new $assertions['class']();
    $component_assert->assertComponent($assertions['expected'], $component_html);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ?array $render_array = NULL, string $separator = NULL): array {
    $form['before']['#markup'] = Markup::create($separator);
    $form['test'] = $render_array;
    $form['after']['#markup'] = Markup::create($separator);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $add_errors = function (array $element) use (&$add_errors, $form_state): void {
      if (!empty($element['#set_validation_error'])) {
        $label = !empty($element['#title']) ? $element['#title'] : implode('][', $element['#array_parents']);
        $form_state->setError($element, t('Validation error on @label', ['@label' => $label]));
      }

      foreach (Element::children($element) as $key) {
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
    return 'oe_bootstrap_theme_component_rendering_test_form';
  }

}
