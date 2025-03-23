<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel;

use Drupal\Core\Template\Attribute;
use Drupal\Core\Url;
use Drupal\oe_bootstrap_theme\MenuPreprocess;
use Drupal\KernelTests\KernelTestBase;

/**
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
   * The MenuPreprocess service under test.
   *
   * @var \Drupal\oe_bootstrap_theme\MenuPreprocess
   */
  protected MenuPreprocess $preprocess;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->preprocess = new MenuPreprocess();
  }

  /**
   * Tests that the 'active' class and custom classes are added to links.
   */
  public function testMenuLinkAddsActiveClass(): void {
    $url = Url::fromRoute('<front>');
    $menu_link = [
      'title' => 'Home',
      'url' => $url,
      'in_active_trail' => TRUE,
    ];

    $result = $this->preprocess->menuLink($menu_link, ['custom-class']);

    $this->assertInstanceOf(Attribute::class, $result['attributes']);
    $this->assertContains('active', $result['attributes']->getClass());
    $this->assertContains('custom-class', $result['attributes']->getClass());
  }

  /**
   * Tests that a <nolink> item with children uses '#' as its path.
   */
  public function testMenuLinkUsesHashForNoLinkWithChildren(): void {
    $url = Url::fromRoute('<nolink>');
    $menu_link = [
      'title' => 'Parent item',
      'url' => $url,
      'below' => [
        ['title' => 'Child item', 'url' => Url::fromRoute('<front>')],
      ],
    ];

    $result = $this->preprocess->menuLink($menu_link);

    $this->assertEquals('#', $result['path']);
  }

  /**
   * Tests that normal external links retain their original URL path.
   */
  public function testMenuLinkKeepsOriginalUrl(): void {
    $url = Url::fromUri('https://example.com');
    $menu_link = [
      'title' => 'External link',
      'url' => $url,
      'below' => [],
    ];

    $result = $this->preprocess->menuLink($menu_link);

    $this->assertSame($url, $result['path']);
  }

  /**
   * Tests that external (non-routed) URLs do not trigger getRouteName().
   */
  public function testMenuLinkWithExternalUrl(): void {
    $url = Url::fromUri('https://example.com');

    $menu_link = [
      'title' => 'External link',
      'url' => $url,
      'below' => [
        ['title' => 'Child item', 'url' => Url::fromRoute('<front>')],
      ],
    ];

    $result = $this->preprocess->menuLink($menu_link);

    $this->assertSame($url, $result['path']);
  }

}
