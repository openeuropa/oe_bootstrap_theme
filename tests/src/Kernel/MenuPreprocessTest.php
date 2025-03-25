<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel;

use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Access\AccessResultForbidden;
use Drupal\Core\Render\Markup;
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

  /**
   * Tests MenuPreprocess::preprocessMenu().
   */
  public function testPreprocessMenu(): void {
    $preprocess = new MenuPreprocess();

    $variables = [
      'items' => [
        [
          'title' => 'Parent 1',
          'url' => Url::fromRoute('<front>'),
          'below' => [
            [
              'title' => 'Child 1',
              'url' => Url::fromRoute('<front>'),
            ],
            [
              'title' => 'Child 2',
              'url' => Url::fromUri('https://example.com'),
            ],
          ],
        ],
        [
          'title' => 'Parent 2 (no children)',
          'url' => Url::fromUri('https://drupal.org'),
        ],
        [
          'title' => 'Parent 3 (no children) and active trail',
          'url' => Url::fromUri('https://drupal.org'),
          'in_active_trail' => TRUE,
        ],
      ],
    ];

    $preprocess->preprocessMenu($variables, 'menu__main');
    $this->assertCount(3, $variables['items']);

    $item1 = $variables['items'][0];
    $this->assertArrayHasKey('standalone', $item1);
    $this->assertTrue($item1['standalone']);
    $this->assertTrue($item1['dropdown']);
    $this->assertTrue($item1['link']);
    $this->assertArrayHasKey('trigger', $item1);
    $this->assertEquals('Parent 1', $item1['trigger']['label']);
    $this->assertInstanceOf(Url::class, $item1['trigger']['path']);
    $this->assertEquals('/', $item1['trigger']['path']->toString());
    $this->assertInstanceOf(Attribute::class, $item1['trigger']['attributes']);
    $this->assertContains('nav-link', $item1['trigger']['attributes']->getClass());
    $this->assertArrayHasKey('items', $item1);
    $this->assertIsArray($item1['items']);
    $this->assertCount(2, $item1['items']);

    // First child.
    $child1 = $item1['items'][0];
    $this->assertEquals('Child 1', $child1['label']);
    $this->assertInstanceOf(Url::class, $child1['path']);
    $this->assertEquals('/', $child1['path']->toString());
    $this->assertInstanceOf(Attribute::class, $child1['attributes']);

    // Second child.
    $child2 = $item1['items'][1];
    $this->assertEquals('Child 2', $child2['label']);
    $this->assertInstanceOf(Url::class, $child2['path']);
    $this->assertEquals('https://example.com', $child2['path']->toString());
    $this->assertInstanceOf(Attribute::class, $child2['attributes']);

    // Second item - no children.
    $item_no_children = $variables['items'][1];
    $this->assertArrayNotHasKey('standalone', $item_no_children);
    $this->assertArrayNotHasKey('dropdown', $item_no_children);
    $this->assertArrayNotHasKey('trigger', $item_no_children);

    // Third item - no children but active trail.
    $item_active = $variables['items'][2];
    $this->assertArrayNotHasKey('standalone', $item_active);
    $this->assertArrayNotHasKey('dropdown', $item_active);
    $this->assertArrayNotHasKey('trigger', $item_active);
    $this->assertArrayHasKey('attributes', $item_active);
    $this->assertInstanceOf(Attribute::class, $item_active['attributes']);
    $this->assertContains('nav-link', $item_active['attributes']->getClass());
    $this->assertContains('active', $item_active['attributes']->getClass());
  }

  /**
   * Tests MenuPreprocess::preprocessMenuLocalAction().
   */
  public function testPreprocessMenuLocalAction(): void {
    $preprocess = new MenuPreprocess();

    $variables = [
      'link' => [
        '#options' => [
          'attributes' => [
            'class' => [],
          ],
        ],
      ],
    ];

    $preprocess->preprocessMenuLocalAction($variables);

    $this->assertContains('btn', $variables['link']['#options']['attributes']['class']);
    $this->assertContains('btn-sm', $variables['link']['#options']['attributes']['class']);
    $this->assertContains('btn-primary', $variables['link']['#options']['attributes']['class']);
  }

  /**
   * Tests MenuPreprocess::preprocessLinksDropbutton().
   */
  public function testPreprocessLinksDropbutton(): void {
    $preprocess = new MenuPreprocess();

    $variables = [
      'links' => [
        'first_link' => [
          'text' => 'Main action',
          'link' => [
            '#url' => Url::fromUri('https://example.com'),
            '#options' => [
              'attributes' => [
                'class' => [],
              ],
            ],
          ],
        ],
        'second_link' => [
          'text' => 'Secondary action',
          'link' => [
            '#url' => Url::fromRoute('<front>'),
            '#options' => [
              'attributes' => [
                'class' => [],
              ],
            ],
          ],
        ],
      ],
    ];

    $preprocess->preprocessLinksDropbutton($variables);

    $this->assertTrue($variables['split']);

    $this->assertArrayHasKey('button', $variables);
    $this->assertIsArray($variables['button'], 'Button array is populated.');
    $this->assertContains('btn', $variables['button']['#options']['attributes']['class']);
    $this->assertContains('btn-sm', $variables['button']['#options']['attributes']['class']);
    $this->assertContains('btn-outline-primary', $variables['button']['#options']['attributes']['class']);

    $this->assertCount(1, $variables['links']);
    $this->assertContains('dropdown-item', $variables['links']['second_link']['link']['#options']['attributes']['class']);
  }

  /**
   * Tests MenuPreprocess::preprocessMenuLocalTasks().
   */
  public function testPreprocessMenuLocalTasks(): void {
    $preprocess = new MenuPreprocess();

    $variables = [
      'primary' => [
        'some_task' => [
          '#access' => AccessResultAllowed::allowed(),
          '#active' => FALSE,
          '#weight' => 5,
          '#link' => [
            'title' => 'Some Task',
            'url' => Url::fromRoute('entity.node.edit_form', ['node' => 1]),
          ],
        ],
        'another_task' => [
          '#access' => AccessResultAllowed::allowed(),
          '#active' => TRUE,
          '#weight' => 2,
          '#link' => [
            'title' => 'Active Task',
            'url' => Url::fromRoute('entity.node.edit_form', ['node' => 2]),
          ],
        ],
        'forbidden_task' => [
          '#access' => AccessResultForbidden::forbidden(),
          '#active' => FALSE,
          '#weight' => 1,
          '#link' => [
            'title' => 'Forbidden Task',
            'url' => Url::fromRoute('entity.node.delete_form', ['node' => 1]),
          ],
        ],
      ],
      'secondary' => [],
    ];

    $preprocess->preprocessMenuLocalTasks($variables);

    $this->assertCount(2, $variables['primary']);
    $this->assertEquals('Active Task', $variables['primary'][0]['label']);
    $this->assertEquals('Some Task', $variables['primary'][1]['label']);

    $this->assertTrue($variables['primary'][0]['attributes']->hasClass('active'));
  }

  /**
   * Tests MenuPreprocess::addDropbuttonClasses().
   */
  public function testAddDropbuttonClasses(): void {
    $preprocess = new MenuPreprocess();

    $links = [
      'first_item' => [
        'link' => [
          '#title' => 'Edit',
          '#url' => Url::fromRoute('entity.node.edit_form', ['node' => 1]),
          '#options' => [
            'attributes' => [
              'class' => ['existing-class'],
            ],
          ],
        ],
      ],
      'second_item' => [
        'text' => [
          '#markup' => Markup::create('Delete'),
          '#attributes' => [
            'class' => [],
          ],
        ],
      ],
    ];

    $preprocess->addDropbuttonClasses($links);

    $this->assertContains('dropdown-item', $links['first_item']['link']['#options']['attributes']['class']);
    $this->assertContains('dropdown-item', $links['second_item']['text']['#attributes']['class']);
  }

}
