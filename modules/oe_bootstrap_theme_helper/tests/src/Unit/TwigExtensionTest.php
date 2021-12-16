<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme_helper\Unit;

use Drupal\Tests\UnitTestCase;
use Twig\Environment;
use Drupal\Core\Template\Loader\StringLoader;
use Drupal\oe_bootstrap_theme_helper\TwigExtension\TwigExtension;

/**
 * Tests for the custom Twig filters and functions extension.
 *
 * @group oe_bootstrap_theme_helper
 *
 * @coversDefaultClass \Drupal\oe_bootstrap_theme_helper\TwigExtension\TwigExtension
 *
 * @group batch1
 */
class TwigExtensionTest extends UnitTestCase {

  /**
   * The Twig environment containing the extension being tested.
   *
   * @var \Twig\Environment
   */
  protected $twig;

  /**
   * The Twig extension being tested.
   *
   * @var \Drupal\oe_bootstrap_theme_helper\TwigExtension\TwigExtension
   */
  protected $extension;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $loader = new StringLoader();
    $this->twig = new Environment($loader);

    $this->extension = new TwigExtension();
    $this->twig->addExtension($this->extension);
  }

  /**
   * Tests converting an icon name to the ECL supported icons.
   *
   * @param array $variables
   *   The input data needed to send to filter.
   * @param array $expected_array
   *   The icon array to be rendered.
   *
   * @covers ::toEclIcon
   * @dataProvider bclMergeIconProvider
   */
  public function testBclMergeIcon(array $variables, array $expected_array): void {
    $items = $variables['items'];
    $size = $variables['size'];
    $path = $variables['path'];
    $result = $this->twig->render("{{ $items|bcl_merge_icon('$size', '$path') }}");
    $this->assertEquals($expected_array, $result);
  }

  /**
   * Returns test cases for ::testBclMergeIcon().
   *
   * @return array[]
   *   An icon array.
   *
   * @see ::testBclMergeIcon()
   */
  public function bclMergeIconProvider(): array {
    return [
      [
        [
          'items' => [
            'icon' => [
              'name' => 'icon1',
            ],
          ],
          'size' => 'xs',
          'path' => '/example1',
        ],
        [
          'icon' => [
            'name' => 'icon1',
            'size' => 'xs',
            'path' => '/example1',
          ],
        ],
      ],
    ];
  }

}
