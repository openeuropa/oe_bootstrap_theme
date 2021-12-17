<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme_helper\Kernel;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Render\RenderContext;
use Drupal\Tests\oe_bootstrap_theme\Kernel\AbstractKernelTestBase;
use Drupal\Tests\oe_bootstrap_theme\Kernel\Traits\RenderTrait;

/**
 * Test those Twig extension filters that require Drupal to be bootstrapped.
 *
 * @group batch2
 */
class TwigExtensionTest extends AbstractKernelTestBase {

  use RenderTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'datetime_testing',
  ];

  /**
   * Test bcl_timeago filter.
   *
   * @param array $variables
   *   Twig variables.
   * @param array $assertions
   *   Test assertions.
   *
   * @dataProvider bclTimeAgoFilterDataProvider
   */
  public function testBclTimeAgoFilter(array $variables, array $assertions): void {
    // Set a fixed time system for testing purposes.
    $static_time = DrupalDateTime::createFromTimestamp('1639484394');
    $this->freezeTime($static_time);

    $elements = [
      '#type' => 'inline_template',
      '#template' => '{{ time|bcl_timeago }}',
      '#context' => [
        'time' => $variables['time'],
      ],
    ];

    $context = new RenderContext();
    $renderer = $this->container->get('renderer');
    $output = $renderer->executeInRenderContext($context, function () use (&$elements, $renderer) {
      return (string) $renderer->render($elements);
    });
    $this->assertRendering($output, $assertions);
  }

  /**
   * Data provider for testBclTimeAgoFilter.
   *
   * @return array
   *   An array of test data arrays with assertions.
   */
  public function bclTimeAgoFilterDataProvider(): array {
    return [
      'N/A' => [
        'variables' => [
          'time' => '1639484394',
        ],
        'assertions' => [
          'equals' => [
            '<body><p>N/A</p></body>',
          ],
        ],
      ],
      // Singular.
      '1 second ago' => [
        'variables' => [
          'time' => '1639484393',
        ],
        'assertions' => [
          'equals' => [
            '<body><p>1 second ago</p></body>',
          ],
        ],
      ],
      '1 minute ago' => [
        'variables' => [
          'time' => '1639484302',
        ],
        'assertions' => [
          'equals' => [
            '<body><p>1 minute ago</p></body>',
          ],
        ],
      ],
      '1 hour ago' => [
        'variables' => [
          'time' => '1639477301',
        ],
        'assertions' => [
          'equals' => [
            '<body><p>1 hour ago</p></body>',
          ],
        ],
      ],
      '1 day ago' => [
        'variables' => [
          'time' => '1639397569',
        ],
        'assertions' => [
          'equals' => [
            '<body><p>1 day ago</p></body>',
          ],
        ],
      ],
      '1 week ago' => [
        'variables' => [
          'time' => '1638879554',
        ],
        'assertions' => [
          'equals' => [
            '<body><p>1 week ago</p></body>',
          ],
        ],
      ],
      '1 month ago' => [
        'variables' => [
          'time' => '1636892354',
        ],
        'assertions' => [
          'equals' => [
            '<body><p>1 month ago</p></body>',
          ],
        ],
      ],
      '1 year ago' => [
        'variables' => [
          'time' => '1607947969',
        ],
        'assertions' => [
          'equals' => [
            '<body><p>1 year ago</p></body>',
          ],
        ],
      ],

      // Plural.
      '2 seconds ago' => [
        'variables' => [
          'time' => '1639484392',
        ],
        'assertions' => [
          'equals' => [
            '<body><p>2 seconds ago</p></body>',
          ],
        ],
      ],
      '2 minutes ago' => [
        'variables' => [
          'time' => '1639484242',
        ],
        'assertions' => [
          'equals' => [
            '<body><p>2 minutes ago</p></body>',
          ],
        ],
      ],
      '2 hours ago' => [
        'variables' => [
          'time' => '1639477042',
        ],
        'assertions' => [
          'equals' => [
            '<body><p>2 hours ago</p></body>',
          ],
        ],
      ],
      '2 days ago' => [
        'variables' => [
          'time' => '1639304242',
        ],
        'assertions' => [
          'equals' => [
            '<body><p>2 days ago</p></body>',
          ],
        ],
      ],
      '2 weeks ago' => [
        'variables' => [
          'time' => '1638181042',
        ],
        'assertions' => [
          'equals' => [
            '<body><p>2 weeks ago</p></body>',
          ],
        ],
      ],
      '2 months ago' => [
        'variables' => [
          'time' => '1634206642',
        ],
        'assertions' => [
          'equals' => [
            '<body><p>2 months ago</p></body>',
          ],
        ],
      ],
      '2 years ago' => [
        'variables' => [
          'time' => '1575973042',
        ],
        'assertions' => [
          'equals' => [
            '<body><p>2 years ago</p></body>',
          ],
        ],
      ],
    ];
  }

  /**
   * Freeze time.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $static_time
   *   Time to freeze.
   */
  protected function freezeTime(DrupalDateTime $static_time): void {
    /** @var \Drupal\datetime_testing\TestTimeInterface $time */
    $time = \Drupal::time();
    $time->freezeTime();
    $time->setTime($static_time->getTimestamp());
  }

}
