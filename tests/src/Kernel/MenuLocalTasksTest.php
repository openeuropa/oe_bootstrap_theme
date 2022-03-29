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
      '#secondary' => [
        'link4.link' => [
          '#theme' => 'menu_local_task',
          '#link' => [
            'title' => 'Sixth link - Active',
            'url' => Url::fromUri('http://www.active.com'),
          ],
          '#active' => TRUE,
          '#weight' => 10,
        ],
        'link5.link' => [
          '#theme' => 'menu_local_task',
          '#link' => [
            'title' => 'Fourth link - Inactive',
            'url' => Url::fromUri('http://www.inactive.com'),
          ],
          '#active' => FALSE,
          '#weight' => -10,
        ],
        'link6.link' => [
          '#theme' => 'menu_local_task',
          '#link' => [
            'title' => 'Fifth link',
            'url' => Url::fromUri('http://www.middlelink.com'),
          ],
          '#active' => FALSE,
          '#weight' => 0,
        ],
      ],
    ];

    $html = $this->renderRoot($render);
    $crawler = new Crawler($html);

    $nav = $crawler->filter('nav.nav-tabs');
    $this->assertCount(2, $nav);

    $links = $crawler->filter('a.nav-link.standalone');
    $this->assertCount(6, $links);

    $active = $crawler->filter('a.active');
    $this->assertCount(2, $active);

    $this->assertEquals('Third link - Active', trim($active->eq(0)->text()));
    $this->assertEquals('Sixth link - Active', trim($active->eq(1)->text()));

    // Assert regular links are ordered by weight.
    $this->assertEquals('First link - Inactive', trim($links->eq(0)->text()));
    $this->assertEquals('Second link', trim($links->eq(1)->text()));
    $this->assertEquals('Third link - Active', trim($links->eq(2)->text()));
    $this->assertEquals('Fourth link - Inactive', trim($links->eq(3)->text()));
    $this->assertEquals('Fifth link', trim($links->eq(4)->text()));
    $this->assertEquals('Sixth link - Active', trim($links->eq(5)->text()));
  }

}
