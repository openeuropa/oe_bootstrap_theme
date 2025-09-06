<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme_helper\Kernel;

use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Menu\MenuActiveTrailInterface;
use Drupal\Core\ProxyClass\Menu\MenuActiveTrail;
use Drupal\Core\Routing\AccessAwareRouterInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Serialization\Yaml;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Url;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\oe_bootstrap_theme_helper\Plugin\Block\MegaMenuBlock;
use Drupal\Tests\oe_bootstrap_theme\Kernel\AbstractKernelTestBase;
use Drupal\Tests\oe_bootstrap_theme\Traits\GetServiceTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

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
   * Weight for newly created menu links.
   *
   * This is incremented each time a new menu link is created.
   */
  protected int $menuLinkWeight = 0;

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
    $expected_items = [];
    $assert = function () use (&$expected_items): void {
      $this->assertBlockPlugin($expected_items);
    };

    $this->addLeaf($expected_items, 'home');
    $assert();

    $parent = $this->addTree($expected_items, 'tree');
    $assert();

    $subtree_parent = $this->addSubtree($expected_items, $parent->getPluginId(), 'subtree');
    $assert();

    // Remove the description on the parent item.
    $this->treeUnsetDescription($expected_items[$parent->getPluginId()], $parent);
    $assert();

    // Set a very long description on the parent item.
    $this->treeSetLongDescription($expected_items[$parent->getPluginId()], $parent);
    $assert();

    // Test descriptions with html.
    $this->treeSetUnsafeDescription($expected_items[$parent->getPluginId()], $parent);
    $assert();

    $item = $this->addLeaf($expected_items, 'unsafe-description-leaf-0');
    $this->leafSetUnsafeDescription($expected_items[$item->getPluginId()], $item);
    $assert();

    // Add some nolink leaf items.
    $this->addLeaf($expected_items, 'nolink-leaf-0', nolink: TRUE);
    $this->addLeaf($expected_items[$parent->getPluginId()]['items'], 'nolink-leaf-1', $parent->getPluginId(), nolink: TRUE);
    $this->addLeaf($expected_items[$parent->getPluginId()]['items'][$subtree_parent->getPluginId()]['items'], 'nolink-leaf-2', $subtree_parent->getPluginId(), 2, nolink: TRUE);
    $assert();

    // Change parent items to <nolink>.
    $this->parentSetNolink($expected_items[$parent->getPluginId()], $parent);
    $assert();
    $this->parentSetNolink($expected_items[$parent->getPluginId()]['items'][$subtree_parent->getPluginId()], $subtree_parent, 1);
    $assert();

    // Add nolink parent items.
    $this->addTree($expected_items, 'nolink-parent', nolink: TRUE);
    $assert();
    $this->addSubtree($expected_items, $parent->getPluginId(), 'nolink-subtree-parent', nolink: TRUE);
    $assert();

    // Create an item that will become the active one.
    $active_page_item = $this->addLeaf($expected_items[$parent->getPluginId()]['items'][$subtree_parent->getPluginId()]['items'], 'active-page', $subtree_parent->getPluginId(), 2);
    $assert();

    // Set an active trail.
    $this->setActivePageUrl('/oebt/example/active-page');
    $expected_items[$parent->getPluginId()]['trigger']['attributes']['class'][] = 'active';
    $expected_items[$parent->getPluginId()]['items'][$subtree_parent->getPluginId()]['trigger']['attributes']['class'][] = 'active';
    $expected_items[$parent->getPluginId()]['items'][$subtree_parent->getPluginId()]['items'][$active_page_item->getPluginId()]['attributes']['class'][] = 'active';
    $assert();
  }

  /**
   * Adds a tree with a single child item.
   *
   * @param array $expected_items
   *   Expected top-level items, to be modified by this method.
   * @param string $name
   *   Name to use when creating the parent link.
   * @param bool $nolink
   *   TRUE for <nolink>, FALSE for a regular link url.
   *
   * @return \Drupal\menu_link_content\Entity\MenuLinkContent
   *   The newly created parent menu link.
   */
  protected function addTree(array &$expected_items, string $name, bool $nolink = FALSE): MenuLinkContent {
    $parent = $this->createExampleMenuLink($name, nolink: $nolink);
    $expected_items[$parent->getPluginId()] = [
      'trigger' => [
        'label' => "Link to '$name'",
        'attributes' => ['class' => ['nav-link']],
      ],
      'items' => [],
      'mega_menu' => TRUE,
      'content_block' => [
        'title' => [
          '#type' => 'html_tag',
          '#tag' => 'h4',
          '#value' => "Link to '$name'",
        ],
        'text' => [
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#value' => "Link description for '$name'",
        ],
      ],
      'content_link' => [
        'label' => 't(Discover more)',
        'path' => [
          // This url has the link title attribute removed.
          'url' => "/oebt/example/$name",
        ],
      ],
    ];
    if ($nolink) {
      unset($expected_items[$parent->getPluginId()]['content_link']);
    }
    // Add a level 1 leaf item.
    $this->addLeaf($expected_items[$parent->getPluginId()]['items'], "$name-leaf-0", $parent->getPluginId());
    return $parent;
  }

  /**
   * Removes the description for a top-level parent item.
   *
   * This should only be called when the item currently has a description.
   *
   * @param array $expected_item
   *   Expected item as in the render element, to be modified.
   * @param \Drupal\menu_link_content\Entity\MenuLinkContent $link
   *   The parent menu link to be modified.
   */
  protected function treeUnsetDescription(array &$expected_item, MenuLinkContent $link): void {
    // Remove the description.
    $link->__unset('description');
    $link->save();
    $this->assertEmpty($link->getDescription());
    // Update expected item.
    $this->assertNotEmpty($expected_item['content_block']['text']);
    unset($expected_item['content_block']['text']);
  }

  /**
   * Sets a long description for a top-level parent item.
   *
   * @param array $expected_item
   *   Expected item as in the render element, to be modified.
   * @param \Drupal\menu_link_content\Entity\MenuLinkContent $link
   *   The parent menu link to be modified.
   */
  protected function treeSetLongDescription(array &$expected_item, MenuLinkContent $link): void {
    // Set a longer description.
    // Use a text that would cause a word to be cut when truncated.
    $link->set('description', 'This is the first sentence. This is the second sentence. This is the third sentence. This is the fourth of the sentences. This is the fifth sentence. This is the sixth sentence. This is the seventh sentence. This is the eighth sentence.');
    $link->save();
    $expected_item['content_block']['text'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      // The description is truncated.
      '#value' => "This is the first sentence. This is the second sentence. This is the third sentence. This is the fourth of the sentences. This is the fifth sentence. This is the sixth se",
    ];
  }

  /**
   * Sets a description with html tags for a top-level parent item.
   *
   * @param array $expected_item
   *   Expected item as in the render element, to be modified.
   * @param \Drupal\menu_link_content\Entity\MenuLinkContent $link
   *   The parent menu link to be modified.
   */
  protected function treeSetUnsafeDescription(array &$expected_item, MenuLinkContent $link): void {
    // Set a description with html.
    $link->set('description', 'A <strong>bold</strong> word in a tree description.');
    $link->save();
    $expected_item['content_block']['text'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      // The description is sanitized.
      '#value' => 'A <strong>bold</strong> word in a tree description.',
    ];
  }

  /**
   * Sets an unsafe description in a leaf item.
   *
   * @param array $expected_item
   *   The expected item to be modified.
   * @param \Drupal\menu_link_content\Entity\MenuLinkContent $link
   *   The link to be modified.
   */
  protected function leafSetUnsafeDescription(array &$expected_item, MenuLinkContent $link): void {
    // Set a description with html.
    $link->set('description', 'A <strong>bold</strong> word in a link description.');
    $link->save();
    // The attribute value still contains the html tags, it will be escaped when
    // the attributes are printed.
    $expected_item['path']['attributes']['title'] = 'A <strong>bold</strong> word in a link description.';
  }

  /**
   * Sets the url of a parent item to <nolink>.
   *
   * This should only be called when the item currently has a regular url.
   *
   * @param array $expected_item
   *   Expected item as in the render element, to be modified.
   * @param \Drupal\menu_link_content\Entity\MenuLinkContent $link
   *   The parent menu link to be modified.
   * @param int $level
   *   Level of the menu link, with 0 for a top-level item.
   *   Only 0 and 1 are supported.
   */
  protected function parentSetNolink(array &$expected_item, MenuLinkContent $link, int $level = 0): void {
    // Set <nolink> url.
    $link->set('link', ['uri' => 'route:<nolink>']);
    $link->save();
    // Update expected item.
    if ($level === 0) {
      $this->assertNotEmpty($expected_item['content_link']['path']);
      unset($expected_item['content_link']);
    }
    elseif ($level === 1) {
      $this->assertNotEmpty($expected_item['see_all']['path']);
      unset($expected_item['see_all']);
    }
    else {
      $this->fail("Unexpected level $level.");
    }
  }

  /**
   * Adds a level 1 item with a single level 2 child item.
   *
   * @param array $expected_items
   *   Expected items, to be modified by this method.
   * @param string $parent_id
   *   The parent link id.
   * @param string $name
   *   Name used to generate the subtree parent link.
   * @param bool $nolink
   *   TRUE for a <nolink> item, FALSE for a regular link.
   *
   * @return \Drupal\menu_link_content\Entity\MenuLinkContent
   *   The newly created subtree parent item.
   */
  protected function addSubtree(array &$expected_items, string $parent_id, string $name, bool $nolink = FALSE): MenuLinkContent {
    // Add a level 1 parent item.
    $subtree_parent = $this->createExampleMenuLink($name, parent: $parent_id);
    $expected_items[$parent_id]['items'][$subtree_parent->getPluginId()] = [
      'trigger' => [
        'label' => "Link to '$name'",
        'attributes' => ['class' => []],
      ],
      'items' => [],
      'see_all' => [
        'label' => "t(More of link to &#039;$name&#039;)",
        'path' => [
          'url' => "/oebt/example/$name",
          'attributes' => [
            'title' => "Link description for '$name'",
          ],
        ],
      ],
    ];
    // Add a level 2 child item.
    // Without this, $name would not be a parent but a leaf item.
    $this->addLeaf($expected_items[$parent_id]['items'][$subtree_parent->getPluginId()]['items'], "$name-leaf-0", $subtree_parent->getPluginId(), 2);
    return $subtree_parent;
  }

  /**
   * Adds a menu item with no children.
   *
   * @param array $expected_items_at_level
   *   Expected items array at the given level, to be modified by this method.
   * @param string $name
   *   Name to use when generating the menu item.
   * @param string|null $parent_id
   *   A string identifying the parent menu link, or NULL for a top-level item.
   * @param int|null $level
   *   The depth/level of the new menu link, or NULL to choose 0 or 1 based on
   *   whether $parent_id is NULL.
   * @param bool $nolink
   *   TRUE for a <nolink> item, FALSE for a regular link item.
   *
   * @return \Drupal\menu_link_content\Entity\MenuLinkContent
   *   The newly created menu link.
   */
  protected function addLeaf(array &$expected_items_at_level, string $name, ?string $parent_id = NULL, ?int $level = NULL, bool $nolink = FALSE): MenuLinkContent {
    $level ??= ($parent_id === NULL) ? 0 : 1;
    $leaf = $this->createExampleMenuLink($name, parent: $parent_id, nolink: $nolink);
    $expected_items_at_level[$leaf->getPluginId()] = [
      'label' => "Link to '$name'",
      'path' => [
        'url' => $nolink ? '' : "/oebt/example/$name",
        'attributes' => [
          'title' => "Link description for '$name'",
        ],
      ],
      'attributes' => match ($level) {
        0 => ['class' => ['nav-link']],
        default => ['class' => []],
      },
    ];
    return $leaf;
  }

  /**
   * Sets an active page for the active trail.
   *
   * @param string $url
   *   Url path.
   */
  protected function setActivePageUrl(string $url): void {
    $request = Request::create($url);
    $this->service(AccessAwareRouterInterface::class)->matchRequest($request);
    $this->service(CurrentRouteMatch::class, RouteMatchInterface::class)->resetRouteMatch();
    $request->setSession(new Session(new MockArraySessionStorage()));
    $this->service(RequestStack::class)->push($request);
    $this->service(MenuActiveTrail::class, MenuActiveTrailInterface::class)->clear();
  }

  /**
   * Asserts the block plugin behavior with the current list of stored links.
   *
   * @param array $expected_items
   *   Expected items for the block render array, normalized for easier
   *   comparison.
   */
  protected function assertBlockPlugin(array $expected_items): void {
    $block_plugin = $this->createBlockPluginInstance('main');
    $this->assertBlockPluginMethods('main', $block_plugin);
    if (!$expected_items) {
      $this->assertEmptyMenu($block_plugin);
    }
    else {
      $this->assertBlockPluginBuild($expected_items, $block_plugin);
    }
  }

  /**
   * Asserts behavior of block plugin methods except ->build().
   *
   * @param string $menu
   *   The menu name.
   * @param \Drupal\oe_bootstrap_theme_helper\Plugin\Block\MegaMenuBlock $block_plugin
   *   The block plugin instance.
   */
  protected function assertBlockPluginMethods(string $menu, MegaMenuBlock $block_plugin): void {
    $this->assertSame(['config:system.menu.' . $menu], $block_plugin->getCacheTags());
    $this->assertSame(['route.menu_active_trails:' . $menu], $block_plugin->getCacheContexts());
    $this->assertSame(-1, $block_plugin->getCacheMaxAge());
    $this->assertSame(['config' => ['system.menu.' . $menu]], $block_plugin->calculateDependencies());
  }

  /**
   * Asserts block output for an empty 'main' menu.
   */
  protected function assertEmptyMenu(MegaMenuBlock $block_plugin): void {
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
    // Call ->analyze() to avoid objects in the comparison.
    // Use yaml for a friendlier output.
    $this->assertSame(
      Yaml::encode([
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
      ]),
      Yaml::encode($this->analyze($block_plugin->build())),
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
        $url_options = $value->getOptions();
        unset($url_options['fragment'], $url_options['query']);
        if ($url_options['set_active_class'] ?? NULL === TRUE) {
          unset($url_options['set_active_class']);
        }
        elseif (!array_key_exists('set_active_class', $url_options)) {
          $url_options['set_active_class'] = '(not set)';
        }
        if (($url_options['attributes']['class'] ?? NULL) === []) {
          unset($url_options['attributes']['class']);
        }
        if (($url_options['attributes'] ?? NULL) === []) {
          unset($url_options['attributes']);
        }
        return ['url' => $value->toString(), ...$url_options];
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
   * @param string|bool $description
   *   The link description, or TRUE to generate one, or FALSE for no
   *   description.
   * @param string|null $parent
   *   A string identifying the parent menu link, or NULL for a top-level item.
   * @param bool $nolink
   *   TRUE for a <nolink> item, FALSE for a regular url.
   * @param int|null $weight
   *   Menu link weight, or NULL for automatic weight.
   *
   * @return \Drupal\menu_link_content\Entity\MenuLinkContent
   *   The new menu link entity.
   */
  protected function createExampleMenuLink(
    string $arg,
    string|null $title = NULL,
    string|bool $description = TRUE,
    ?string $parent = NULL,
    bool $nolink = FALSE,
    ?int $weight = NULL,
  ): MenuLinkContent {
    $menu_link = MenuLinkContent::create(array_filter([
      'title' => $title ?? "Link to '$arg'",
      'description' => match ($description) {
        TRUE => "Link description for '$arg'",
        FALSE => NULL,
        default => $description,
      },
      'menu_name' => 'main',
      'parent' => $parent,
      'link' => ['uri' => $nolink ? 'route:<nolink>' : 'internal:/oebt/example/' . $arg],
      'weight' => $weight ?? ++$this->menuLinkWeight,
    ], fn ($value) => $value !== NULL));
    $menu_link->save();
    return $menu_link;
  }

}
