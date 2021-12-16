<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme_helper\Unit;

use Drupal\Tests\UnitTestCase;
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

    $filter = new TwigExtension();

    $result = $filter->bclMergeIcon($items, $size, $path);
    $this->assertSame($expected_array, $result);
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
      [
        [
          'items' => [
            [
              'term' => [
                0 => [
                  'label' => 'label1',
                  'icon' => [
                    'name' => 'name1',
                  ],
                ],
              ],
              'definition' => 'definition1',
            ],
            [
              'term' => [
                0 => [
                  'label' => 'label2',
                ],
                1 => [
                  'label' => 'label3',
                  'icon' => [
                    'name' => 'name3',
                  ],
                ],
              ],
              'definition' => 'definition2',
            ],
          ],
          'size' => 'xs',
          'path' => '/example1',
        ],
        [
          [
            'term' => [
              0 => [
                'label' => 'label1',
                'icon' => [
                  'name' => 'name1',
                  'size' => 'xs',
                  'path' => '/example1',
                ],
              ],
            ],
            'definition' => 'definition1',
          ],
          [
            'term' => [
              0 => [
                'label' => 'label2',
              ],
              1 => [
                'label' => 'label3',
                'icon' => [
                  'name' => 'name3',
                  'size' => 'xs',
                  'path' => '/example1',
                ],
              ],
            ],
            'definition' => 'definition2',
          ],
        ],
      ],
      [
        [
          'items' => [
            'icon' => [
              'name' => 'icon1',
            ],
          ],
          'size' => NULL,
          'path' => NULL,
        ],
        [
          'icon' => [
            'name' => 'icon1',
          ],
        ],
      ],
      [
        [
          'items' => [
            [
              'term' => [
                0 => [
                  'label' => 'label1',
                ],
              ],
              'definition' => 'definition1',
            ],
            [
              'term' => [
                0 => [
                  'label' => 'label2',
                ],
                1 => [
                  'label' => 'label3',
                ],
              ],
              'definition' => 'definition2',
            ],
          ],
          'size' => 'xs',
          'path' => '/example1',
        ],
        [
          [
            'term' => [
              0 => [
                'label' => 'label1',
              ],
            ],
            'definition' => 'definition1',
          ],
          [
            'term' => [
              0 => [
                'label' => 'label2',
              ],
              1 => [
                'label' => 'label3',
              ],
            ],
            'definition' => 'definition2',
          ],
        ],
      ],
    ];
  }

}
