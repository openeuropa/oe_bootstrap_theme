<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Unit;

use Drupal\Tests\oe_bootstrap_theme\Helper\HtmlSnapshotNormalizer;
use PHPUnit\Framework\TestCase;

/**
 * Tests the HtmlNormalizer class.
 *
 * @coversDefaultClass \Drupal\Tests\oe_bootstrap_theme\Helper\HtmlSnapshotNormalizer
 * @group oe_bootstrap_theme
 */
class HtmlSnapshotNormalizerTest extends TestCase {

  /**
   * Tests HTML normalization.
   *
   * @param string $html
   *   The original HTML.
   * @param string $expected
   *   The expected normalized HTML.
   *
   * @dataProvider htmlProvider
   */
  public function testNormalize(string $html, string $expected): void {
    $normalizer = new HtmlSnapshotNormalizer();
    $normalized = $normalizer->normalize($html);
    self::assertSame($expected, $normalized);
  }

  /**
   * Provides HTML normalization test cases.
   *
   * @return array<string, array{string, string}>
   *   The test cases.
   */
  public static function htmlProvider(): array {
    return [
      'empty string' => [
        '',
        '',
      ],
      'whitespace only' => [
        " \n\t ",
        '',
      ],
      'simple HTML' => [
        '<div>Hello World</div>',
        '<div>Hello World</div>',
      ],
      'normalizes whitespace' => [
        "<div>\n  Hello   World  \n</div>",
        '<div>Hello World</div>',
      ],
      'removes whitespace between block elements' => [
        "<div>First</div>\n  <div>Second</div>",
        '<div>First</div><div>Second</div>',
      ],
      'preserves whitespace between inline elements' => [
        '<span>First</span>   <span>Second</span>',
        '<span>First</span> <span>Second</span>',
      ],
      'removes whitespace around block elements' => [
        "<div>\n  <p>Hello</p>\n  <p>World</p>\n</div>",
        '<div><p>Hello</p><p>World</p></div>',
      ],
      'preserves whitespace in pre' => [
        "<pre>\n  Hello   World\n</pre>",
        "<pre>\n  Hello   World\n</pre>",
      ],
      'preserves whitespace in textarea' => [
        "<textarea>\n  Hello   World\n</textarea>",
        "<textarea>\n  Hello   World\n</textarea>",
      ],
      'preserves whitespace in script' => [
        "<script>\n  const value = 'Hello   World';\n</script>",
        "<script>\n  const value = 'Hello   World';\n</script>",
      ],
      'preserves whitespace in style' => [
        "<style>\n  .foo {  color: red;  }\n</style>",
        "<style>\n  .foo {  color: red;  }\n</style>",
      ],
      'sorts attributes alphabetically' => [
        '<div id="foo" class="bar" title="baz"></div>',
        '<div class="bar" id="foo" title="baz"></div>',
      ],
      'sorts classes alphabetically' => [
        '<div class="zebra apple mango"></div>',
        '<div class="apple mango zebra"></div>',
      ],
      'sorts attributes and classes' => [
        '<div title="foo" class="zebra apple" id="bar"></div>',
        '<div class="apple zebra" id="bar" title="foo"></div>',
      ],
      'does not double escape entities' => [
        '<div>Tom &amp; Jerry</div>',
        '<div>Tom &amp; Jerry</div>',
      ],
      'escapes angle brackets in text' => [
        '<div>1 < 2 and 3 > 2</div>',
        '<div>1 &lt; 2 and 3 &gt; 2</div>',
      ],
      'preserves UTF-8 characters' => [
        '<div>© € ä ö ü Grüße</div>',
        '<div>© € ä ö ü Grüße</div>',
      ],
      // PHP DOMDocument does not treat 'required' as boolean, therefore it
      // becomes 'required=""'.
      'normalizes boolean attributes, part 1' => [
        '<input disabled required>',
        '<input disabled required="">',
      ],
      'normalizes boolean attributes, part 2' => [
        '<input disabled="disabled" required="required">',
        '<input disabled required="required">',
      ],
      'normalizes boolean attributes, part 3' => [
        '<input disabled="" required="">',
        '<input disabled required="">',
      ],
      'normalizes boolean attributes, part 4' => [
        '<input disabled="xyz" required="xyz">',
        '<input disabled required="xyz">',
      ],
      'returns only body contents' => [
        '<html><head><title>Title</title></head><body><div>Hello</div></body></html>',
        '<div>Hello</div>',
      ],
    ];
  }

  /**
   * Tests that element callbacks are invoked for every descendant element.
   */
  public function testElementCallback(): void {
    $processed = [];

    $normalizer = (new HtmlSnapshotNormalizer())
      ->withElementCallback(static function (\DOMElement $element) use (&$processed): void {
        $processed[] = $element->tagName;
        $element->setAttribute('data-processed', 'true');
      });

    $actual = $normalizer->normalize(
      '<div><p><span>Hello</span></p></div>'
    );

    self::assertSame(
      '<div data-processed="true"><p data-processed="true"><span data-processed="true">Hello</span></p></div>',
      $actual,
    );

    self::assertSame(['div', 'p', 'span'], $processed);
  }

  /**
   * Tests that callbacks are immutable.
   */
  public function testWithElementCallbackIsImmutable(): void {
    $original = new HtmlSnapshotNormalizer();

    $withCallback = $original->withElementCallback(
      static function (\DOMElement $element): void {
        $element->setAttribute('data-processed', 'true');
      }
    );

    self::assertSame(
      '<div></div>',
      $original->normalize('<div></div>'),
    );

    self::assertSame(
      '<div data-processed="true"></div>',
      $withCallback->normalize('<div></div>'),
    );
  }

}
