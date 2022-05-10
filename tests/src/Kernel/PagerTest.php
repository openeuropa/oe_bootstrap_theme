<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel;

use Drupal\Tests\oe_bootstrap_theme\PatternAssertion\PaginationPatternAssert;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests rendering of the pager.
 */
class PagerTest extends AbstractKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'oe_bootstrap_theme_helper',
    'system',
    'ui_patterns',
    'ui_patterns_settings',
    'user',
    'views',
  ];

  /**
   * Tests a pager with custom labels for prev/next and first/last.
   *
   * This reflects pagers in views, where custom labels can be specified in the
   * configuration.
   */
  public function testCustomLabels(): void {
    \Drupal::request()->query->set('page', '2');

    /** @var \Drupal\Core\Pager\PagerManagerInterface $pager_manager */
    $pager_manager = $this->container->get('pager.manager');
    $pager_manager->createPager(75, 10);

    $element = [
      '#type' => 'pager',
      '#quantity' => 3,
      '#tags' => [
        // Use labels that are distinguishable from default labels.
        0 => 'The first',
        1 => 'The previous',
        3 => 'The next',
        4 => 'The last',
      ],
    ];
    $html = $this->renderRoot($element);

    $pagination_assert = new PaginationPatternAssert();

    $pagination_assert->assertPattern([
      'alignment' => 'center',
      'links' => [
        // The bcl-pagination component always shows icons for first/last.
        ['url' => '?page=0', 'icon' => 'chevron-double-left'],
        ['url' => '?page=1', 'label' => 'The previous'],
        ['url' => '?page=1', 'label' => '2'],
        ['url' => '?page=2', 'label' => '3', 'active' => TRUE],
        ['url' => '?page=3', 'label' => '4'],
        ['url' => '?page=3', 'label' => 'The next'],
        ['url' => '?page=7', 'icon' => 'chevron-double-right'],
      ],
    ], $html);
  }

  /**
   * Tests a pager when other pagers and url parameters are also present.
   */
  public function testExtraUrlParams(): void {
    \Drupal::request()->query->set('x', 'X');
    \Drupal::request()->query->set('page', '0,1,2,7');
    \Drupal::request()->query->set('y', 'Y');

    /** @var \Drupal\Core\Pager\PagerManagerInterface $pager_manager */
    $pager_manager = $this->container->get('pager.manager');
    $pager_manager->createPager(75, 10);
    $pager_manager->createPager(75, 10, 1);
    $pager_manager->createPager(75, 10, 2);
    $pager_manager->createPager(75, 10, 3);

    $element = [
      '#type' => 'pager',
      '#quantity' => 3,
      '#element' => 2,
      '#route_name' => 'user.admin_index',
    ];
    $html = $this->renderRoot($element);

    $pagination_assert = new PaginationPatternAssert();

    $get_url = static function (int $page): string {
      return '/admin/config/people?x=X&y=Y&page=' . urlencode("0,1,$page,7");
    };

    $pagination_assert->assertPattern([
      'alignment' => 'center',
      'links' => [
        ['url' => $get_url(0), 'icon' => 'chevron-double-left'],
        ['url' => $get_url(1), 'label' => 'Previous'],
        ['url' => $get_url(1), 'label' => '2'],
        ['url' => $get_url(2), 'label' => '3', 'active' => TRUE],
        ['url' => $get_url(3), 'label' => '4'],
        ['url' => $get_url(3), 'label' => 'Next'],
        ['url' => $get_url(7), 'icon' => 'chevron-double-right'],
      ],
    ], $html);
  }

  /**
   * Tests the pager.html.twig theme override.
   *
   * @param array $expected_args
   *   Arguments for a pattern assertion.
   * @param int $page
   *   Current page number.
   *
   * @dataProvider pagerDataProvider
   */
  public function testPager(array $expected_args, int $page): void {
    \Drupal::request()->query->set('page', $page);

    /** @var \Drupal\Core\Pager\PagerManagerInterface $pager_manager */
    $pager_manager = $this->container->get('pager.manager');
    $pager_manager->createPager(75, 10);

    $element = [
      '#type' => 'pager',
      '#quantity' => 3,
    ];
    $html = $this->renderRoot($element);

    $pagination_assert = new PaginationPatternAssert();

    $pagination_assert->assertPattern($expected_args, $html);
  }

  /**
   * Data provider.
   *
   * @return array[]
   *   Argument lists.
   */
  public function pagerDataProvider(): array {
    $scenarios = [
      0 => [
        ['url' => '?page=0', 'label' => '1', 'active' => TRUE],
        ['url' => '?page=1', 'label' => '2'],
        ['url' => '?page=2', 'label' => '3'],
        ['url' => '?page=1', 'label' => 'Next'],
        ['url' => '?page=7', 'icon' => 'chevron-double-right'],
      ],
      1 => [
        ['url' => '?page=0', 'icon' => 'chevron-double-left'],
        ['url' => '?page=0', 'label' => 'Previous'],
        ['url' => '?page=0', 'label' => '1'],
        ['url' => '?page=1', 'label' => '2', 'active' => TRUE],
        ['url' => '?page=2', 'label' => '3'],
        ['url' => '?page=2', 'label' => 'Next'],
        ['url' => '?page=7', 'icon' => 'chevron-double-right'],
      ],
      7 => [
        ['url' => '?page=0', 'icon' => 'chevron-double-left'],
        ['url' => '?page=6', 'label' => 'Previous'],
        ['url' => '?page=5', 'label' => '6'],
        ['url' => '?page=6', 'label' => '7'],
        ['url' => '?page=7', 'label' => '8', 'active' => TRUE],
      ],
    ];
    $datasets = [];
    foreach ($scenarios as $page => $links) {
      $datasets[$page] = [
        [
          'alignment' => 'center',
          'links' => $links,
        ],
        $page,
      ];
    }
    return $datasets;
  }

  /**
   * Tests the views-mini-pager.html.twig theme override.
   *
   * @param array $expected_args
   *   Arguments for a pattern assertion.
   * @param int $page
   *   Current page number.
   *
   * @dataProvider viewsMiniPagerDataProvider
   */
  public function testViewsMiniPager(array $expected_args, int $page): void {
    \Drupal::requestStack()->push(
      Request::create('/admin', 'GET', [
        'page' => $page,
      ]),
    );

    /** @var \Drupal\Core\Pager\PagerManagerInterface $pager_manager */
    $pager_manager = $this->container->get('pager.manager');
    $pager_manager->createPager(75, 10);

    $element = [
      '#theme' => 'views_mini_pager',
      '#tags' => [
        1 => 'The previous',
        3 => 'The next',
      ],
    ];
    $html = $this->renderRoot($element);

    $pagination_assert = new PaginationPatternAssert();

    $pagination_assert->assertPattern($expected_args, $html);
  }

  /**
   * Data provider.
   *
   * @return array[]
   *   Argument lists.
   */
  public function viewsMiniPagerDataProvider(): array {
    $scenarios = [
      0 => [
        ['url' => '', 'label' => '1'],
        ['url' => '/?page=1', 'label' => 'The next'],
      ],
      1 => [
        ['url' => '/?page=0', 'label' => 'The previous'],
        ['url' => '', 'label' => '2'],
        ['url' => '/?page=2', 'label' => 'The next'],
      ],
      7 => [
        ['url' => '/?page=6', 'label' => 'The previous'],
        ['url' => '', 'label' => '8'],
      ],
    ];
    $datasets = [];
    foreach ($scenarios as $page => $links) {
      $datasets[$page] = [
        [
          'alignment' => 'center',
          'links' => $links,
        ],
        $page,
      ];
    }
    return $datasets;
  }

}
