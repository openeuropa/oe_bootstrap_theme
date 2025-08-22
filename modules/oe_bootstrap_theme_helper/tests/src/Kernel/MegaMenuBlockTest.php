<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme_helper\Kernel;

use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Url;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\oe_bootstrap_theme_helper\Plugin\Block\MegaMenuBlock;
use Drupal\Tests\oe_bootstrap_theme\Kernel\AbstractKernelTestBase;
use Drupal\Tests\oe_bootstrap_theme\Traits\GetServiceTrait;

/**
 * Test the OEL Mega Menu block plugin.
 *
 * @coversDefaultClass \Drupal\oe_bootstrap_theme_helper\Plugin\Block\MegaMenuBlock
 */
class MegaMenuBlockTest extends AbstractKernelTestBase {

  use GetServiceTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
    'link',
    'menu_link_content',
    'oe_bootstrap_theme_route_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('menu_link_content');
  }

  /**
   * Tests the block build method.
   */
  public function testMegaMenuBlock(): void {
    $block_plugin = $this->createBlockPluginInstance('main');
    // Test a block for an empty menu.
    $this->assertSame([
      '#cache' => [
        'contexts' => [],
        // For an empty menu there are no cache tags, because this is what
        // SystemMenuBlock returns.
        // It is odd, but mitigated by the fact that ->getCacheTags() still
        // returns them.
        'tags' => [],
        'max-age' => -1,
      ],
    ], $block_plugin->build());
    $this->assertBlockPluginMethods('main', $block_plugin);
    // Add a single link.
    $a = $this->createExampleMenuLink('link-a');
    $create_expected_link = fn (string $arg) => [
      'label' => "Link to '$arg'",
      'path' => "/oebt/example/$arg",
      'attributes' => ['class' => ['nav-link']],
    ];
    // Test a block with a single item.
    $this->assertBlockPluginMethods('main', $block_plugin);
    $this->assertBlockPluginBuild([
      $a => $create_expected_link('link-a'),
    ], $block_plugin);
    $b = $this->createExampleMenuLink('link-b');
    $c = $this->createExampleMenuLink('link-c');
    $c1 = $this->createExampleMenuLink('link-c-1', parent: $c);
    $c2 = $this->createExampleMenuLink('link-c-2', parent: $c);
    $c2x = $this->createExampleMenuLink('link-c-2-x', parent: $c2);
    $c2y = $this->createExampleMenuLink('link-c-2-y', parent: $c2);
    $c3 = $this->createExampleMenuLink('link-c-3', parent: $c);
    $d = $this->createExampleMenuLink('link-d', description: FALSE);
    $d1 = $this->createExampleMenuLink('link-d-1', parent: $d);
    // Test with a tree of items.
    $create_expected_child_link = fn (string $arg) => [
      'label' => "Link to '$arg'",
      'path' => "/oebt/example/$arg",
      'attributes' => ['class' => []],
    ];
    $this->assertBlockPluginBuild([
      $a => $create_expected_link('link-a'),
      $b => $create_expected_link('link-b'),
      $c => [
        'trigger' => [
          'label' => "Link to 'link-c'",
          'attributes' => ['class' => ['nav-link']],
        ],
        'items' => [
          $c1 => $create_expected_child_link('link-c-1'),
          $c2 => [
            'trigger' => [
              'label' => "Link to 'link-c-2'",
              'attributes' => ['class' => []],
            ],
            'items' => [
              $c2x => $create_expected_child_link('link-c-2-x'),
              $c2y => $create_expected_child_link('link-c-2-y'),
            ],
            'see_all' => [
              'label' => "t(More of link to &#039;link-c-2&#039;)",
              'path' => '/oebt/example/link-c-2',
            ],
          ],
          $c3 => $create_expected_child_link('link-c-3'),
        ],
        'mega_menu' => TRUE,
        'content_block' => [
          'title' => [
            '#type' => 'html_tag',
            '#tag' => 'h4',
            '#value' => "Link to 'link-c'",
          ],
          'text' => [
            '#type' => 'html_tag',
            '#tag' => 'p',
            '#value' => "Link description for 'link-c'",
          ],
        ],
        'content_link' => [
          'label' => 't(Discover more)',
          'path' => '/oebt/example/link-c',
        ],
      ],
      $d => [
        'trigger' => [
          'label' => "Link to 'link-d'",
          'attributes' => ['class' => ['nav-link']],
        ],
        'items' => [
          $d1 => $create_expected_child_link('link-d-1'),
        ],
        'mega_menu' => TRUE,
        'content_block' => [
          'title' => [
            '#type' => 'html_tag',
            '#tag' => 'h4',
            '#value' => "Link to 'link-d'",
          ],
        ],
        'content_link' => [
          'label' => 't(Discover more)',
          'path' => '/oebt/example/link-d',
        ],
      ],
    ], $block_plugin);
  }

  /**
   * Asserts behavior of block plugin methods except ->build().
   *
   * @param string $menu
   *   The menu name.
   * @param MegaMenuBlock $block_plugin
   *   The block plugin instance.
   */
  protected function assertBlockPluginMethods(string $menu, MegaMenuBlock $block_plugin): void {
    $this->assertSame(['config:system.menu.' . $menu], $block_plugin->getCacheTags());
    $this->assertSame(['route.menu_active_trails:' . $menu], $block_plugin->getCacheContexts());
    $this->assertSame(-1, $block_plugin->getCacheMaxAge());
    $this->assertSame(['config' => ['system.menu.' . $menu]], $block_plugin->calculateDependencies());
  }

  /**
   * Asserts the render element from a block plugin.
   *
   * @param array $expected_items
   *   Expected items in $build['#fields']['items'].
   * @param \Drupal\oe_bootstrap_theme_helper\Plugin\Block\MegaMenuBlock $block_plugin
   *   The block plugin.
   */
  protected function assertBlockPluginBuild(array $expected_items, MegaMenuBlock $block_plugin): void {
    $this->assertSame(
      [
        '#type' => 'pattern',
        '#id' => 'navigation',
        '#variant' => 'navbar',
        '#fields' => [
          'items' => $expected_items,
        ],
        '#cache' => [
          'contexts' => [],
          'tags' => ['config:system.menu.main'],
          'max-age' => -1,
        ],
      ],
      $this->analyze($block_plugin->build()),
    );
  }

  /**
   * Creates an instance of MegaMenuBlock.
   *
   * @param string $menu
   *   The menu name to use.
   *
   * @return \Drupal\oe_bootstrap_theme_helper\Plugin\Block\MegaMenuBlock
   *   The block plugin instance.
   */
  protected function createBlockPluginInstance(string $menu): MegaMenuBlock {
    $block_plugin = $this->service(BlockManagerInterface::class)->createInstance(
      'oel_mega_menu',
      [
        'menu' => $menu,
      ],
    );
    $this->assertInstanceOf(MegaMenuBlock::class, $block_plugin);
    return $block_plugin;
  }

  /**
   * Processes a value before it is compared in an assertion.
   *
   * Objects are replaced by strings or arrays, to avoid an overly verbose
   * assertion output.
   *
   * @param mixed $value
   *   The value to clean up.
   *
   * @return mixed
   *   Cleaned up value.
   */
  protected function analyze(mixed $value): mixed {
    if (is_array($value)) {
      return array_map($this->analyze(...), $value);
    }
    if (is_object($value)) {
      if (get_class($value) === Url::class) {
        return $value->toString();
      }
      if (get_class($value) === Attribute::class) {
        return $value->toArray();
      }
      if (get_class($value) === TranslatableMarkup::class) {
        return "t($value)";
      }
      return get_class($value) . ' object';
    }
    return $value;
  }

  /**
   * Creates an example menu link.
   *
   * @param string $arg
   *   A string to be used in the url and in link title and description.
   * @param string|null $title
   *   The link title, or NULL to generate a title from $arg.
   * @param string|null|false $description
   *   The link description, or NULL to generate one, or FALSE for no
   *   description.
   * @param string|null $parent
   *   A string identifying the parent menu link, or NULL for a top-level item.
   *
   * @return string
   *   Identifier for this menu link, to be passed as parent when creating child
   *   links.
   */
  protected function createExampleMenuLink(
    string $arg,
    string|null $title = NULL,
    string|null|false $description = NULL,
    ?string $parent = NULL,
  ): string {
    $link = MenuLinkContent::create(array_filter([
      'title' => $title ?? "Link to '$arg'",
      'description' => match ($description) {
        NULL => "Link description for '$arg'",
        FALSE => NULL,
        default => $description,
      },
      'menu_name' => 'main',
      'parent' => $parent,
      'link' => ['uri' => 'internal:/oebt/example/' . $arg],
    ], fn ($value) => $value !== NULL));
    $link->save();
    return 'menu_link_content:' . $link->uuid();
  }

}
