<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel;

use Drupal\block\Entity\Block;
use Drupal\menu_link_content\Entity\MenuLinkContent;

/**
 * Tests the main navigation block accessibility markup.
 *
 * @group oe_bootstrap_theme
 */
class MainNavigationBlockTest extends AbstractKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
    'link',
    'menu_link_content',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('menu_link_content');
  }

  /**
   * Ensures the main navigation block has an aria-label and no extra heading.
   */
  public function testMainNavigationMarkup(): void {
    $this->createMainMenuLink();

    $block = $this->createMainNavigationBlock();

    $build = $this->container->get('entity_type.manager')
      ->getViewBuilder('block')
      ->view($block, 'block');
    $html = $this->renderRoot($build);

    $this->assertRendering($html, [
      'count' => [
        '.me-auto[role="navigation"][aria-label="Main navigation menu"]' => 1,
        'h2' => 0,
      ],
    ]);
    $this->assertStringNotContainsString('Main navigation bar', $html);
  }

  /**
   * Creates a simple main menu link so the block renders content.
   */
  protected function createMainMenuLink(): MenuLinkContent {
    $menu_link = MenuLinkContent::create([
      'title' => 'Home',
      'menu_name' => 'main',
      'link' => [
        'uri' => 'internal:/',
      ],
    ]);
    $menu_link->save();

    return $menu_link;
  }

  /**
   * Creates the main navigation block using the theme defaults.
   */
  protected function createMainNavigationBlock(): Block {
    $block = Block::create([
      'id' => 'test_main_navigation',
      'plugin' => 'system_menu_block:main',
      'theme' => 'oe_bootstrap_theme',
      'region' => 'navbar_left',
      'weight' => -3,
      'status' => TRUE,
      'settings' => [
        'id' => 'system_menu_block:main',
        'label' => 'Main navigation menu',
        'label_display' => FALSE,
        'provider' => 'system',
        'level' => 1,
        'depth' => 0,
        'expand_all_items' => FALSE,
      ],
      'visibility' => [],
    ]);
    $block->save();

    return $block;
  }

}
