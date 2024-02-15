<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel;

use Drupal\Core\Url;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests that Drupal dropbutton links are properly rendered.
 */
class DropbuttonTest extends AbstractKernelTestBase {

  /**
   * Test dropbutton links.
   */
  public function testDropbutton(): void {
    $render = [
      '#type' => 'dropbutton',
      '#links' => [
        'button' => [
          'title' => 'Dropbutton',
          'url' => Url::fromRoute('<front>'),
        ],
        'one' => [
          'title' => 'Link 1',
          'url' => Url::fromRoute('<front>'),
        ],
        'two' => [
          'title' => 'Link 2',
          'url' => Url::fromRoute('<front>'),
        ],
        'three' => [
          'title' => 'Link 3',
          'url' => Url::fromRoute('<front>'),
        ],
        'four' => [
          'title' => 'Link 4',
          'url' => Url::fromRoute('<front>'),
        ],
        'five' => [
          'title' => 'Link 5',
          'url' => Url::fromRoute('<front>'),
        ],
      ],
    ];

    $html = $this->renderRoot($render);
    $crawler = new Crawler($html);

    $group = $crawler->filter('div.btn-group');
    $this->assertCount(1, $group);

    $button = $crawler->filter('button.btn.btn-sm.btn-outline-primary');
    $this->assertCount(1, $button);

    $list = $crawler->filter('ul.dropdown-menu');
    $this->assertCount(1, $list);

    $items = $crawler->filter('li > a.dropdown-item');
    $this->assertCount(5, $items);
    $this->assertEquals('Link 1', trim($items->eq(0)->text()));
    $this->assertEquals('Link 2', trim($items->eq(1)->text()));
    $this->assertEquals('Link 3', trim($items->eq(2)->text()));
    $this->assertEquals('Link 4', trim($items->eq(3)->text()));
    $this->assertEquals('Link 5', trim($items->eq(4)->text()));
  }

}
