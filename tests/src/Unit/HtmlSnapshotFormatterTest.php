<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\Tests\oe_bootstrap_theme\Helper\HtmlSnapshotFormatter;

/**
 * Tests the HtmlFormatter class.
 *
 * @coversDefaultClass \Drupal\Tests\oe_bootstrap_theme\Helper\HtmlSnapshotFormatter
 */
class HtmlSnapshotFormatterTest extends UnitTestCase {

  /**
   * Tests formatting HTML.
   *
   * @param string $html
   *   The input HTML.
   * @param string $expected
   *   The expected formatted HTML.
   *
   * @dataProvider formatDataProvider
   */
  public function testFormat(string $html, string $expected): void {
    $formatter = new HtmlSnapshotFormatter();
    $this->assertSame(
      // Prepend line breaks for nicer diff output.
      "\n" . $expected,
      "\n" . $formatter->format($html),
    );
  }

  /**
   * Provides test cases for HTML formatting.
   *
   * @return array
   *   An array of test cases.
   */
  public static function formatDataProvider(): array {
    return [
      'empty string' => [
        '',
        '',
      ],
      'whitespace only' => [
        " \n\t ",
        '',
      ],
      'single empty element' => [
        '<div></div>',
        "<div></div>\n",
      ],
      'single element with text' => [
        '<div>Hello world</div>',
        "<div>\n  Hello world\n</div>\n",
      ],
      'nested elements' => [
        '<div><p>Hello</p><p>World</p></div>',
        "<div>\n  <p>\n    Hello\n  </p>\n  <p>\n    World\n  </p>\n</div>\n",
      ],
      'multiple root elements' => [
        '<div>First</div><span>Second</span>',
        "<div>\n  First\n</div>\n<span>\n  Second\n</span>\n",
      ],
      'attributes' => [
        '<div id="example" class="container">Content</div>',
        "<div\n  id=\"example\"\n  class=\"container\"\n>\n  Content\n</div>\n",
      ],
      'boolean attributes' => [
        '<input disabled checked="checked" required>',
        "<input\n  disabled\n  checked\n  required\n/>\n",
      ],
      'boolean attribute with explicit (bad) value' => [
        '<button disabled="false">Click</button>',
        "<button\n  disabled=\"false\"\n>\n  Click\n</button>\n",
      ],
      'attribute escaping' => [
        '<div title="Tom &amp; Jerry &quot;special&quot;"></div>',
        "<div\n  title=\"Tom &amp; Jerry &quot;special&quot;\"\n></div>\n",
      ],
      'void elements' => [
        '<div><br><hr><img src="image.png"></div>',
        "<div>\n  <br />\n  <hr />\n  <img\n    src=\"image.png\"\n  />\n</div>\n",
      ],
      'text with surrounding whitespace' => [
        '<div>  Hello world  </div>',
        "<div>\n\n  Hello world\n\n</div>\n",
      ],
      'text with internal whitespace' => [
        '<div>Hello   world</div>',
        "<div>\n  Hello   world\n</div>\n",
      ],
      'text with line breaks' => [
        "<div>\n  Hello\n  World\n</div>",
        "<div>\n\n  Hello\n    World\n\n</div>\n",
      ],
      'mixed text and elements' => [
        '<div>Before<span>Inside</span>After</div>',
        "<div>\n  Before\n  <span>\n    Inside\n  </span>\n  After\n</div>\n",
      ],
      'pre preserves whitespace' => [
        "<pre>  Hello\n    World</pre>",
        "<pre>  Hello\n    World</pre>",
      ],
      'textarea preserves whitespace' => [
        "<textarea>  Hello\n    World</textarea>",
        "<textarea>  Hello\n    World</textarea>",
      ],
      'script preserves whitespace' => [
        '<script>  const value = 1;</script>',
        '<script>  const value = 1;</script>',
      ],
      'style preserves whitespace' => [
        '<style>  .example { color: red; }</style>',
        '<style>  .example { color: red; }</style>',
      ],
      'special characters in text' => [
        '<div>Tom &amp; Jerry</div>',
        "<div>\n  Tom &amp; Jerry\n</div>\n",
      ],
      'comments' => [
        '<div><!-- A comment --><span>Text</span></div>',
        "<div>\n  <!-- A comment -->\n  <span>\n    Text\n  </span>\n</div>\n",
      ],
    ];
  }

}
