<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme_helper\Kernel;

use Drupal\Core\Render\RenderContext;
use Drupal\Tests\oe_bootstrap_theme\Kernel\AbstractKernelTestBase;
use Twig\Error\RuntimeError;

/**
 * Test those Twig extension filters that require Drupal to be bootstrapped.
 */
class TwigExtensionTest extends AbstractKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
  ];

  /**
   * Test to_native_language filter.
   *
   * @dataProvider toNativeLanguageNameProvider
   */
  public function testToNativeLanguageFilter(string $language_code, string $expected_native_language_name): void {
    $elements = [
      '#type' => 'inline_template',
      '#template' => '{{ language_code|to_native_language }}',
      '#context' => [
        'language_code' => $language_code,
      ],
    ];

    $context = new RenderContext();
    $renderer = $this->container->get('renderer');
    $output = $renderer->executeInRenderContext($context, function () use (&$elements, $renderer) {
      return (string) $renderer->render($elements);
    });

    $this->assertEquals($expected_native_language_name, $output);
  }

  /**
   * Test to_file_icon filter.
   *
   * @dataProvider toFileIconProvider
   */
  public function testToFileIconFilter(string $file_type, string $expected_icon): void {
    $elements = [
      '#type' => 'inline_template',
      '#template' => '{{ file_extension|to_file_icon }}',
      '#context' => [
        'file_extension' => $file_type,
      ],
    ];

    $context = new RenderContext();
    $renderer = $this->container->get('renderer');
    $output = $renderer->executeInRenderContext($context, function () use (&$elements, $renderer) {
      return (string) $renderer->render($elements);
    });

    $this->assertEquals($expected_icon, $output);
  }

  /**
   * Test invalid language codes when converting to the native language name.
   *
   * @dataProvider invalidLanguageCodesProvider
   */
  public function testPassingInvalidLanguageCodesToNativeLanguageName($invalid_language_code): void {
    $this->expectException(RuntimeError::class);

    $elements = [
      '#type' => 'inline_template',
      '#template' => '{{ language_code|to_native_language }}',
      '#context' => [
        'language_code' => $invalid_language_code,
      ],
    ];

    $context = new RenderContext();
    $renderer = $this->container->get('renderer');
    $renderer->executeInRenderContext($context, function () use (&$elements, $renderer) {
      return (string) $renderer->render($elements);
    });
  }

  /**
   * Returns test cases for ::testToNativeLanguageName().
   *
   * @return array[]
   *   An array of test cases, each test case an indexed array with the
   *   following two values:
   *   1. The language code to check.
   *   2. The expected native language name.
   *
   * @see ::testToNativeLanguageName()
   */
  public function toNativeLanguageNameProvider(): array {
    return [
      ['bg', 'български'],
      ['cs', 'čeština'],
      ['da', 'dansk'],
      ['de', 'Deutsch'],
      ['et', 'eesti'],
      ['el', 'ελληνικά'],
      ['en', 'English'],
      ['es', 'español'],
      ['fr', 'français'],
      ['ga', 'Gaeilge'],
      ['hr', 'hrvatski'],
      ['it', 'italiano'],
      ['lt', 'lietuvių'],
      ['lv', 'latviešu'],
      ['hu', 'magyar'],
      ['mt', 'Malti'],
      ['nl', 'Nederlands'],
      ['pl', 'polski'],
      ['pt-pt', 'português'],
      ['ro', 'română'],
      ['sk', 'slovenčina'],
      ['sl', 'slovenščina'],
      ['fi', 'suomi'],
      ['sv', 'svenska'],
    ];
  }

  /**
   * Returns invalid language codes to use as test cases.
   *
   * @return array[]
   *   An array of test cases, each test case an indexed array with a single
   *   value consisting of an invalid language code.
   *
   * @see ::testPassingInvalidLanguageCodesToNativeLanguageName()
   */
  public function invalidLanguageCodesProvider(): array {
    return [
      [NULL],
      [TRUE],
      [FALSE],
      [''],
      ['qq'],
      [-1e10],
      ['≈ç√∫˜µ≤≥'],
      [0],
      ['😍'],
      ['1;DROP TABLE users'],
    ];
  }

  /**
   * Returns test cases for ::testToFileIconFilter().
   *
   * @return array[]
   *   An array of test cases, each test case an indexed array with the
   *   following two values:
   *   1. The extension file to check.
   *   2. The expected icon according to the extension file.
   *
   * @see ::testToFileIconFilter()
   */
  public function toFileIconProvider(): array {
    return [
      ['jpg', 'image'],
      ['jpeg', 'image'],
      ['gif', 'image'],
      ['png', 'image'],
      ['webp', 'image'],
      ['ppt', 'presentation'],
      ['pptx', 'presentation'],
      ['pps', 'presentation'],
      ['ppsx', 'presentation'],
      ['odp', 'presentation'],
      ['xls', 'file-excel-fill'],
      ['xlsx', 'file-excel-fill'],
      ['ods', 'file-excel-fill'],
      ['mp4', 'video'],
      ['mp4', 'video'],
      ['mpeg', 'video'],
      ['avi', 'video'],
      ['m4v', 'video'],
      ['webm', 'video'],
      ['txt', 'file-text-fill'],
      ['pdf', 'file-pdf-fill'],
      ['application/pdf', 'file-pdf-fill'],
      ['word', 'file-word-fill'],
      ['zzz', 'file-earmark'],
      ['tar', 'file-earmark'],
      ['zip', 'file-earmark'],
      ['', 'file-earmark'],
    ];
  }

}
