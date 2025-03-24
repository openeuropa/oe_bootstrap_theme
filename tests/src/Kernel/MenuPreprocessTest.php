<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel;

use Drupal\Core\Template\Attribute;
use Drupal\Core\Url;
use Drupal\oe_bootstrap_theme\MenuPreprocess;
use Drupal\KernelTests\KernelTestBase;

/**
 * @covers \Drupal\oe_bootstrap_theme\MenuPreprocess
 *
 * Tests the MenuPreprocess service logic.
 */
class MenuPreprocessTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
  ];

  /**
   * Tests various scenarios for MenuPreprocess::menuLink().
   *
   * @dataProvider menuLinkProvider
   */
  public function testMenuLink(array $menu_link, array $expected_classes, $expected_path = NULL): void {
    $preprocess = new MenuPreprocess();
    $result = $preprocess->menuLink($menu_link, $menu_link['classes'] ?? []);

    $this->assertInstanceOf(Attribute::class, $result['attributes']);
    foreach ($expected_classes as $class) {
      $this->assertContains($class, $result['attributes']->getClass());
    }

    if ($expected_path !== NULL) {
      $actual_path = $result['path'] instanceof Url ? $result['path']->toString() : $result['path'];
      $expected_path_str = $expected_path instanceof Url ? $expected_path->toString() : $expected_path;

      $this->assertSame($expected_path_str, $actual_path);
    }
  }

  /**
   * Provides test cases for testMenuLink().
   */
  public function menuLinkProvider(): array {
    return [
      'Active link with custom class' => [
        [
          'title' => 'Home',
          'url' => Url::fromRoute('<front>'),
          'in_active_trail' => TRUE,
          'classes' => ['custom-class'],
        ],
        ['active', 'custom-class'],
        '/',
      ],
      'Internal link' => [
        [
          'title' => 'Internal link',
          'url' => Url::fromRoute('<front>'),
          'below' => [],
        ],
        [],
        '/',
      ],
      'External link' => [
        [
          'title' => 'External link',
          'url' => Url::fromUri('https://example.com'),
          'below' => [],
        ],
        [],
        'https://example.com',
      ],
      'Nolink' => [
        [
          'title' => 'No link item',
          'url' => Url::fromRoute('<nolink>'),
          'below' => [],
        ],
        [],
        Url::fromRoute('<nolink>'),
      ],
      'Internal link with children' => [
        [
          'title' => 'Internal link with children',
          'url' => Url::fromRoute('<front>'),
          'below' => [
            ['title' => 'Child item', 'url' => Url::fromRoute('<front>')],
          ],
        ],
        [],
        '/',
      ],
      'External link with children' => [
        [
          'title' => 'External link with children',
          'url' => Url::fromUri('https://example.com'),
          'below' => [
            ['title' => 'Child item', 'url' => Url::fromRoute('<front>')],
          ],
        ],
        [],
        'https://example.com',
      ],
      'Nolink with children' => [
        [
          'title' => 'Nolink item with children',
          'url' => Url::fromRoute('<nolink>'),
          'below' => [
            ['title' => 'Child item', 'url' => Url::fromRoute('<front>')],
          ],
        ],
        [],
        '#',
      ],
    ];
  }

}
