<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme_strict_sdc_validation\Kernel;

use Drupal\Core\Render\Component\Exception\InvalidComponentException;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\Theme\Component\ComponentValidator;
use Drupal\Core\Theme\ComponentPluginManager;
use Drupal\KernelTests\KernelTestBase;
use Drupal\oe_bootstrap_theme_strict_sdc_validation\StrictPropsComponentValidator;

/**
 * Tests strict prop validation for components of opted-in extensions.
 *
 * @coversDefaultClass \Drupal\oe_bootstrap_theme_strict_sdc_validation\StrictPropsComponentValidator
 *
 * @group oe_bootstrap_theme
 */
class StrictPropsComponentValidatorTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'oe_bootstrap_theme_helper',
    'oe_bootstrap_theme_strict_sdc_validation',
    'sdc_test',
    'system',
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

    if (!ini_get('zend.assertions') || ini_get('zend.assertions') === '-1') {
      $this->markTestSkipped('Component validation only runs with PHP assertions enabled.');
    }
  }

  /**
   * Tests validateProps() for opted-in and other components.
   *
   * @param string $component_id
   *   The component id.
   * @param array $props
   *   Props to validate.
   * @param bool $expect_rejection
   *   TRUE if the props should be rejected.
   *
   * @dataProvider validatePropsProvider
   */
  public function testValidateProps(string $component_id, array $props, bool $expect_rejection): void {
    $validator = $this->container->get(ComponentValidator::class);
    $this->assertInstanceOf(StrictPropsComponentValidator::class, $validator);
    $component = $this->container->get(ComponentPluginManager::class)->find($component_id);

    if ($expect_rejection) {
      $this->expectException(InvalidComponentException::class);
    }
    $this->assertTrue($validator->validateProps($props, $component));
  }

  /**
   * Data provider.
   *
   * @return array<string, array{string, array, bool}>
   *   Test cases.
   */
  public static function validatePropsProvider(): array {
    return [
      'opted in, boolean prop, boolean' => ['oe_bootstrap_theme:banner', ['hero' => TRUE], FALSE],
      'opted in, boolean prop, associative array' => ['oe_bootstrap_theme:banner', ['hero' => ['label' => 'x']], TRUE],
      'opted in, boolean prop, list of render arrays' => [
        'oe_bootstrap_theme:banner',
        ['hero' => [['#markup' => 'A'], ['#markup' => 'B']]],
        TRUE,
      ],
      'opted in, array prop, array' => ['oe_bootstrap_theme:banner', ['attributes' => ['class' => ['x']]], FALSE],
      'opted in, null' => ['oe_bootstrap_theme:banner', ['hero' => NULL], FALSE],
      'opted in, string prop, string' => ['oe_bootstrap_theme:banner', ['image' => 'https://example.com/a.png'], FALSE],
      'opted in, string prop, render array' => ['oe_bootstrap_theme:banner', ['image' => ['#markup' => 'x']], TRUE],
      'opted in, string prop, wrong scalar' => ['oe_bootstrap_theme:banner', ['image' => FALSE], TRUE],
      'not opted in, string prop, associative array' => ['sdc_test:my-cta', ['text' => ['label' => 'x']], FALSE],
    ];
  }

  /**
   * Tests that rendering a component applies the strict validation.
   */
  public function testRenderingRejectsArrayInBooleanProp(): void {
    $element = [
      '#type' => 'component',
      '#component' => 'oe_bootstrap_theme:banner',
      '#props' => ['hero' => ['label' => 'x']],
    ];
    try {
      $this->container->get(RendererInterface::class)->renderRoot($element);
    }
    catch (\Throwable $e) {
      // Twig may wrap the exception.
      for ($current = $e; $current; $current = $current->getPrevious()) {
        if ($current instanceof InvalidComponentException) {
          $this->assertStringContainsString('Strict validation failed for component "oe_bootstrap_theme:banner"', $current->getMessage());
          $this->assertStringContainsString('[hero]', $current->getMessage());
          return;
        }
      }
      throw $e;
    }
    $this->fail('Rendering did not reject an array in a boolean prop.');
  }

}
