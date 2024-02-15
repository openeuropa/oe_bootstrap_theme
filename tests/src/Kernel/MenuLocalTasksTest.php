<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests that Drupal local tasks are properly rendered.
 */
class MenuLocalTasksTest extends AbstractKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
    'link',
    'oe_bootstrap_theme_menu_test',
  ];

  /**
   * Test menu local tasks.
   */
  public function testMenuLocalTasks(): void {
    $entity_type_manager = $this->container
      ->get('entity_type.manager');
    $entity = $entity_type_manager->getStorage('block')->load('oe_bootstrap_theme_local_tasks');
    $builder = $entity_type_manager->getViewBuilder('block');
    $build = $builder->view($entity, 'block');
    $render = $this->container->get('renderer')->renderRoot($build);
    $crawler = new Crawler((string) $render);

    $primary = $crawler->filter('nav.nav-tabs');
    $links = $primary->filter('a');
    $this->assertElementsTexts([
      'Task 11',
      'Task 12',
    ], $links);
    $secondary = $crawler->filter('nav.nav-pills');
    $links = $secondary->filter('a');
    $this->assertElementsTexts([
      'Task 21',
      'Task 22',
      'Task 25',
    ], $links);
  }

  /**
   * Asserts text contents for multiple elements at once.
   *
   * @param string[] $expected
   *   Expected element texts.
   * @param \Symfony\Component\DomCrawler\Crawler $elements
   *   Elements to compare.
   */
  protected function assertElementsTexts(array $expected, Crawler $elements): void {
    $items = [];
    /** @var \DOMElement $element */
    foreach ($elements as $element) {
      $items[] = $element->textContent;
    }
    $this->assertSame($expected, $items);
  }

}
