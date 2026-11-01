<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme_helper\Unit;

use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Template\Attribute;
use Drupal\oe_bootstrap_theme\ValueObject\ImageValueObject;
use Drupal\oe_bootstrap_theme\ValueObject\ImageValueObjectInterface;
use Drupal\oe_bootstrap_theme_helper\TwigExtension\TwigExtension;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\AssertionFailedError;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\ArrayLoader;
use Twig\TwigFunction;

/**
 * Tests Twig filters from the helper module in real Twig templates.
 *
 * This uses a plain Twig environment without Drupal, so that every case can
 * be a separate data provider record, without a Drupal bootstrap. The values
 * returned by the filters are captured with a test-only 'capture()' function.
 *
 * Filters and functions that need Drupal services are tested in the kernel
 * test.
 *
 * @see \Drupal\Tests\oe_bootstrap_theme_helper\Kernel\TwigExtensionTest
 */
class TwigExtensionFiltersTest extends UnitTestCase {

  /**
   * Icon path returned by the mocked TwigExtension::getBclIconPath().
   */
  protected const ICON_PATH = '/icons.svg';

  /**
   * Values passed to the 'capture()' function in the template.
   *
   * @var list<mixed>
   */
  protected array $captured = [];

  /**
   * Tests filters that return a value.
   *
   * @param string $expression
   *   Twig expression to evaluate.
   * @param array $context
   *   Variables for the template.
   * @param mixed $expected
   *   Expected value of the expression.
   *
   * @dataProvider filterProvider
   */
  public function testFilter(string $expression, array $context, mixed $expected): void {
    $this->assertEquals($expected, $this->evaluate($expression, $context));
  }

  /**
   * Data provider for testFilter().
   *
   * @return array<string, array{string, array, mixed}>
   *   Test cases.
   */
  public static function filterProvider(): array {
    $path = static::ICON_PATH;
    $image = ['src' => 'https://example.com/image.png', 'alt' => 'Alt text'];
    return [
      // Filter '|bcl_icon'.
      'bcl_icon: string' => [
        "'home'|bcl_icon",
        [],
        ['name' => 'home', 'path' => $path],
      ],
      'bcl_icon: string, with size' => [
        "'home'|bcl_icon(size: 'xs')",
        [],
        ['name' => 'home', 'path' => $path, 'size' => 'xs'],
      ],
      'bcl_icon: array with name only' => [
        "{name: 'home'}|bcl_icon",
        [],
        ['name' => 'home', 'path' => $path],
      ],
      'bcl_icon: array with own path and size' => [
        "{name: 'home', path: '/other.svg', size: 'l'}|bcl_icon(size: 'xs')",
        [],
        ['name' => 'home', 'path' => '/other.svg', 'size' => 'l'],
      ],
      'bcl_icon: array with path NULL gets default path' => [
        "{name: 'home', path: null}|bcl_icon",
        [],
        ['name' => 'home', 'path' => $path],
      ],
      'bcl_icon: other keys are kept' => [
        "{name: 'home', transformation: 'rotate-90'}|bcl_icon",
        [],
        ['name' => 'home', 'transformation' => 'rotate-90', 'path' => $path],
      ],
      'bcl_icon: attributes array becomes Attribute object' => [
        "{name: 'home', attributes: {class: ['a']}}|bcl_icon",
        [],
        [
          'name' => 'home',
          'attributes' => new Attribute(['class' => ['a']]),
          'path' => $path,
        ],
      ],
      'bcl_icon: empty attributes are removed' => [
        "{name: 'home', attributes: []}|bcl_icon",
        [],
        ['name' => 'home', 'path' => $path],
      ],
      'bcl_icon: NULL' => ['null|bcl_icon', [], NULL],
      'bcl_icon: empty string' => ["''|bcl_icon", [], NULL],
      'bcl_icon: empty array' => ['[]|bcl_icon', [], NULL],
      // Filter '|oe_bt__to_image_value_object'.
      'oe_bt__to_image_value_object: array' => [
        'image|oe_bt__to_image_value_object',
        ['image' => $image],
        ImageValueObject::fromArray($image),
      ],
      // Filter '|oe_bt__to_render_array'.
      'oe_bt__to_render_array: image' => [
        'image|oe_bt__to_image_value_object|oe_bt__to_render_array',
        ['image' => $image + ['name' => 'Title']],
        [
          '#theme' => 'image',
          '#uri' => 'https://example.com/image.png',
          '#title' => 'Title',
          '#alt' => 'Alt text',
          '#cache' => [
            'contexts' => [],
            'tags' => [],
            'max-age' => -1,
          ],
        ],
      ],
    ];
  }

  /**
   * Tests filters that throw an error.
   *
   * @param string $expression
   *   Twig expression to evaluate.
   * @param array $context
   *   Variables for the template.
   * @param class-string<\Twig\Error\Error> $exception_class
   *   Expected exception class.
   * @param string $message
   *   Expected exception message, without the template name and line.
   *
   * @dataProvider filterErrorProvider
   */
  public function testFilterError(string $expression, array $context, string $exception_class, string $message): void {
    try {
      $this->evaluate($expression, $context);
    }
    catch (Error $e) {
      if ($exception_class !== get_class($e)) {
        throw new AssertionFailedError("Expected $exception_class, found:", previous: $e);
      }
      $this->assertSame($message, $e->getRawMessage());
      // The error is reported for the template line, not for PHP code.
      $this->assertSame('test.html.twig', $e->getSourceContext()?->getName());
      $this->assertSame(1, $e->getTemplateLine());
      return;
    }
    $this->fail('No exception was thrown.');
  }

  /**
   * Data provider for testFilterError().
   *
   * @return array<string, array{string, array, class-string<\Twig\Error\Error>, string}>
   *   Test cases.
   */
  public static function filterErrorProvider(): array {
    $named_only = 'Expected only named parameters from here on, found string.';
    return [
      // Filter '|bcl_icon'.
      'bcl_icon: size as positional argument' => [
        "'home'|bcl_icon('xs')",
        [],
        RuntimeError::class,
        $named_only,
      ],
      'bcl_icon: unknown named argument' => [
        "'home'|bcl_icon(color: 'red')",
        [],
        SyntaxError::class,
        'Unknown argument "color" for filter "bcl_icon(_, size)".',
      ],
      'bcl_icon: array without name' => [
        "{size: 'xs'}|bcl_icon",
        [],
        RuntimeError::class,
        'Missing icon name in non-empty icon array.',
      ],
      'bcl_icon: integer' => [
        '5|bcl_icon',
        [],
        RuntimeError::class,
        'Expected an array, string or NULL, found int',
      ],
      'bcl_icon: attributes string' => [
        "{name: 'home', attributes: 'x'}|bcl_icon",
        [],
        RuntimeError::class,
        'Expected an array, an Attribute object, or NULL. Found string.',
      ],
      // Filter '|oe_bt__to_image_value_object'.
      'oe_bt__to_image_value_object: empty array' => [
        '[]|oe_bt__to_image_value_object',
        [],
        RuntimeError::class,
        'The image array is empty.',
      ],
      'oe_bt__to_image_value_object: array without src' => [
        "{alt: 'Alt text'}|oe_bt__to_image_value_object",
        [],
        RuntimeError::class,
        "Missing array key 'src'.",
      ],
      'oe_bt__to_image_value_object: string' => [
        "'image.png'|oe_bt__to_image_value_object",
        [],
        RuntimeError::class,
        'Expected ImageValueObjectInterface|array, found string',
      ],
      // Filter '|oe_bt__to_render_array'.
      'oe_bt__to_render_array: array' => [
        "{src: 'image.png'}|oe_bt__to_render_array",
        [],
        RuntimeError::class,
        'Expected a RenderableInterface instance. Found array',
      ],
    ];
  }

  /**
   * Tests '|oe_bt__to_image_value_object' with an object.
   */
  public function testToImageValueObjectWithObject(): void {
    $values = [
      'name' => 'Title',
      'src' => 'https://example.com/image.png',
      'alt' => 'Alt text',
      'responsive' => FALSE,
    ];
    // A different implementation of ImageValueObjectInterface.
    $original = $this->createMock(ImageValueObjectInterface::class);
    $original->method('getArray')->willReturn($values);
    $original->method('getCacheTags')->willReturn(['file:1']);
    $original->method('getCacheContexts')->willReturn(['example-cache-context']);
    $original->method('getCacheMaxAge')->willReturn(60);

    $result = $this->evaluate('image|oe_bt__to_image_value_object', ['image' => $original]);

    $this->assertSame(ImageValueObject::class, get_class($result));
    $this->assertSame($values, $result->getArray());
    $this->assertSame(['file:1'], $result->getCacheTags());
    $this->assertSame(['example-cache-context'], $result->getCacheContexts());
    $this->assertSame(60, $result->getCacheMaxAge());
  }

  /**
   * Renders a Twig expression, and returns its value.
   *
   * @param string $expression
   *   Twig expression.
   * @param array $context
   *   Variables for the template.
   *
   * @return mixed
   *   The value of the expression.
   *
   * @throws \Twig\Error\Error
   */
  protected function evaluate(string $expression, array $context): mixed {
    $this->setMockContainer();
    $extension = $this->createPartialMock(TwigExtension::class, ['getBclIconPath']);
    $extension->method('getBclIconPath')->willReturn(static::ICON_PATH);
    $twig = new Environment(
      new ArrayLoader(['test.html.twig' => '{{ capture(' . $expression . ') }}']),
      ['strict_variables' => TRUE, 'cache' => FALSE],
    );
    $twig->addExtension($extension);
    $twig->addFunction(new TwigFunction('capture', function (mixed $value): string {
      $this->captured[] = $value;
      return '';
    }));
    $this->captured = [];
    $twig->render('test.html.twig', $context);
    $this->assertCount(1, $this->captured);
    return $this->captured[0];
  }

  /**
   * Sets a mock container with the necessary services.
   */
  protected function setMockContainer(): void {
    $container = new ContainerBuilder();
    $cache_contexts_manager = new CacheContextsManager(
      $container,
      [
        'example-cache-context',
      ],
    );
    $container->set('cache_contexts_manager', $cache_contexts_manager);
    \Drupal::setContainer($container);
  }

}
