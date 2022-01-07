<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel;

use Drupal\Core\Url;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests that Drupal local tasks are properly rendered.
 */
class MenuLocalTasksTest extends AbstractKernelTestBase {

  /**
   * Test menu local tasks.
   */
  public function testMenuLocalTasks(): void {
    $render = [
      '#theme' => 'menu_local_tasks',
      '#primary' => [
        'link1.link' => [
          '#theme' => 'menu_local_task',
          '#link' => [
            'title' => 'Third link - Active',
            'url' => Url::fromUri('http://www.active.com'),
          ],
          '#active' => TRUE,
          '#weight' => 10,
        ],
        'link2.link' => [
          '#theme' => 'menu_local_task',
          '#link' => [
            'title' => 'First link - Inactive',
            'url' => Url::fromUri('http://www.inactive.com'),
          ],
          '#active' => FALSE,
          '#weight' => -10,
        ],
        'link3.link' => [
          '#theme' => 'menu_local_task',
          '#link' => [
            'title' => 'Second link',
            'url' => Url::fromUri('http://www.middlelink.com'),
          ],
          '#active' => FALSE,
          '#weight' => 0,
        ],
      ],
    ];

    $html = $this->renderRoot($render);
    $crawler = new Crawler($html);

    $actual = $crawler->filter('nav.nav-tabs');
    $this->assertCount(1, $actual);

    $links = $crawler->filter('a.nav-link.text-underline-hover');
    $this->assertCount(3, $links);

    $active = $crawler->filter('a.active');
    $this->assertCount(1, $active);
    $this->assertEquals('Third link - Active', trim($active->text()));

    // Assert regular link are ordered by weight.
    $this->assertEquals('First link - Inactive', trim($links->eq(0)->text()));
    $this->assertEquals('Second link', trim($links->eq(1)->text()));
    $this->assertEquals('Third link - Active', trim($links->eq(2)->text()));
  }

}
