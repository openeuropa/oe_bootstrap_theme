<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Site\Settings;
use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\DomCrawler\Crawler;

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
  ];

  /**
   * List of SDC components to be tested.
   *
   * @var string[]
   */
  private static $componentList = [
    'alert',
    'description_list',
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
  public function testComponentRendering(array $render, array $assertions): void {
    $form_state = new FormState();
    $form_state->addBuildInfo('args', [$render]);
    $form_state->setProgrammed();

    $form = $this->container->get('form_builder')->buildForm($this, $form_state);
    $this->assertComponentRendering($assertions, (string) $this->container->get('renderer')->renderRoot($form));
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
        $test_cases[$candidate_key] = $test_case;
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
    $component_html = $crawler->filter('body > form')->html();

    /** @var \Drupal\Tests\oe_bootstrap_theme\ComponentAssertion\ComponentAssertInterface $component_assert */
    $component_assert = new $assertions['class']();
    $component_assert->assertComponent($assertions['expected'], $component_html);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ?array $render_array = NULL): array {
    $form['test'] = $render_array;
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
