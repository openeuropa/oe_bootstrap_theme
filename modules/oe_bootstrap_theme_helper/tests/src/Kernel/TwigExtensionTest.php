<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme_helper\Kernel;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Url;
use Drupal\Tests\oe_bootstrap_theme\Kernel\AbstractKernelTestBase;
use Drupal\oe_bootstrap_theme\ValueObject\ImageValueObject;
use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Component\DomCrawler\Crawler;
use Twig\Markup;

/**
 * Test those Twig extensions that require Drupal to be bootstrapped.
 */
class TwigExtensionTest extends AbstractKernelTestBase {

  /**
   * Test bcl_link function.
   */
  public function testBclLinkFunction(): void {
    $items = $this->bclLinkDataProvider();

    foreach ($items as $item) {
      [$expected, $data] = $item;
      $elements = [
        '#type' => 'inline_template',
        '#template' => '{{ bcl_link(label, path, attributes) }}',
        '#context' => [
          'label' => $data['label'],
          'path' => $data['path'],
          'attributes' => new Attribute($data['attributes'] ?? []),
        ],
      ];

      $output = $this->renderRoot($elements);
      $crawler = new Crawler($output);
      $link = $crawler->filter('a');

      $this->assertEquals($expected['href'], $link->attr('href'));
      $this->assertEquals($expected['text'], $link->html());

      if (!empty($expected['attributes'])) {
        foreach ($expected['attributes'] as $name => $value) {
          $this->assertEquals($value, $link->attr($name));
        }
      }
    }
  }

  /**
   * Data provider for bclLinkFunction.
   *
   * @return array
   *   An array of test data arrays with assertions.
   */
  public function bclLinkDataProvider(): array {
    return [
      'string_path_to_front' => [
        [
          'href' => '/',
          'text' => 'My link',
        ],
        [
          'path' => '/',
          'label' => 'My link',
        ],
      ],
      'string_path_front_with_hash' => [
        [
          'href' => '/#kitty',
          'text' => 'Miau',
        ],
        [
          'path' => '/#kitty',
          'label' => 'Miau',
        ],
      ],
      'string_path_current_url_with_hash' => [
        [
          'href' => '#kitty',
          'text' => 'Miau',
        ],
        [
          'path' => '#kitty',
          'label' => 'Miau',
        ],
      ],
      'just_hash' => [
        [
          'href' => '#',
          'text' => 'hash',
        ],
        [
          'path' => '#',
          'label' => 'hash',
        ],
      ],
      'string_path_current_url_with_parameters' => [
        [
          'href' => '?foo=baz',
          'text' => 'My link',
        ],
        [
          'path' => '?foo=baz',
          'label' => 'My link',
        ],
      ],
      'string_path_internal' => [
        [
          'href' => '/some-path',
          'text' => 'Some link',
        ],
        [
          'path' => '/some-path',
          'label' => 'Some link',
        ],
      ],
      'label_with_safe_markup' => [
        [
          'href' => '/',
          'text' => 'This is <em>markup</em>!',
        ],
        [
          'path' => '/',
          // Emulate markup as sent by twig.
          'label' => new Markup('This is <em>markup</em>!', NULL),
        ],
      ],
      'label_with_unsafe_markup' => [
        [
          'href' => '/',
          // Xss filtered markup.
          'text' => 'This is unsafe <em>markup</em>!',
        ],
        [
          'path' => '/',
          'label' => 'This is <script type="text/javascript">unsafe</script> <em>markup</em>!',
        ],
      ],
      'url_object' => [
        [
          'href' => '/node/add',
          'text' => 'Url object link',
        ],
        [
          'path' => Url::fromUserInput('/node/add'),
          'label' => 'Url object link',
        ],
      ],
      'url_object_absolute' => [
        [
          'href' => 'http://localhost/here',
          'text' => 'Absolute url link',
        ],
        [
          'path' => Url::fromUri('base:here', ['absolute' => TRUE]),
          'label' => 'Absolute url link',
        ],
      ],
      'link_with_custom_attribute' => [
        [
          'href' => '/',
          'text' => 'Custom attrib link',
        ],
        [
          'path' => '/',
          'label' => 'Custom attrib link',
          'attributes' => [
            'data-foo' => 'baz',
            'id' => 'id-bar',
          ],
        ],
      ],
      'external_link' => [
        [
          'href' => 'https://www.example.com',
          'text' => 'External link',
        ],
        [
          'path' => 'https://www.example.com',
          'label' => 'External link',
        ],
      ],
      'url with array label' => [
        [
          'href' => 'https://www.example.com/template/',
          'text' => 'user: admin',
        ],
        [
          'path' => 'https://www.example.com/template/',
          'label' => [
            '#type' => 'inline_template',
            '#template' => '{{ prefix }}: {{ suffix }}',
            '#context' => [
              'prefix' => 'user',
              'suffix' => 'admin',
            ],
          ],
        ],
      ],
      'url with array label and url object' => [
        [
          'href' => '/',
          'text' => 'user: anonymous',
        ],
        [
          'path' => Url::fromRoute('<front>'),
          'label' => [
            '#type' => 'inline_template',
            '#template' => '{{ prefix }}: {{ suffix }}',
            '#context' => [
              'prefix' => 'user',
              'suffix' => 'anonymous',
            ],
          ],
        ],
      ],
      'url with array #markup label' => [
        [
          'href' => 'https://www.example.com/markup/',
          'text' => '<b>test</b>',
        ],
        [
          'path' => 'https://www.example.com/markup/',
          'label' => [
            '#markup' => '<b>test</b>',
          ],
        ],
      ],
    ];
  }

  /**
   * Tests the "element_children" Twig filter.
   */
  public function testElementChildrenFilter(): void {
    $required_cache_contexts = $this->container->getParameter('renderer.config')['required_cache_contexts'];
    $renderer = $this->container->get('renderer');

    try {
      foreach ($this->elementChildrenFilterDataProvider() as $scenario => $data) {
        [$items, $expected_output] = $data;
        $expected_bubbled_metadata = [];
        BubbleableMetadata::createFromRenderArray($items)
          ->addCacheContexts($required_cache_contexts)
          ->applyTo($expected_bubbled_metadata);

        $build = [
          '#type' => 'inline_template',
          '#template' => '{{ items|element_children }}',
          '#context' => [
            'items' => $items,
          ],
        ];

        $render_context = new RenderContext();
        $output = $renderer->executeInRenderContext($render_context, function () use ($renderer, $build) {
          return $renderer->render($build, TRUE);
        });

        $bubbled_metadata = [];
        $render_context->pop()->applyTo($bubbled_metadata);
        $this->assertEqualsCanonicalizing($expected_bubbled_metadata, $bubbled_metadata);
        $this->assertEquals($expected_output, $output);
      }
    }
    catch (ExpectationFailedException $e) {
      throw new ExpectationFailedException(sprintf('Failed asserting data for scenario "%s": %s', $scenario, $e->getMessage()), $e->getComparisonFailure(), $e);
    }
    catch (\Exception $e) {
      throw new \Exception(sprintf('Failed asserting data for scenario "%s".', $scenario), 0, $e);
    }
  }

  /**
   * Test the sorting parameter on "element_children" Twig filter.
   */
  public function testElementChildrenFilterSorting(): void {
    $get_build = function ($sort) {
      $template = <<<TWIG
{% for item in items|element_children($sort) %}
  {{- item -}}
{% endfor %}
TWIG;

      return [
        '#type' => 'inline_template',
        '#template' => $template,
        '#context' => [
          'items' => [
            [
              '#plain_text' => 'Weight 20.',
              '#weight' => 20,
            ],
            [
              '#plain_text' => 'Weight -3.',
              '#weight' => -3,
            ],
          ],
        ],
      ];
    };

    $build = $get_build('true');
    $output = $this->renderRoot($build);
    $this->assertEquals('Weight -3.Weight 20.', $output);

    $build = $get_build('false');
    $output = $this->renderRoot($build);
    $this->assertEquals('Weight 20.Weight -3.', $output);
  }

  /**
   * Data provider for testElementChildrenFilter().
   *
   * @return array
   *   The scenarios.
   */
  protected function elementChildrenFilterDataProvider(): array {
    $scenarios = [];

    $basic_build = [
      [
        '#plain_text' => 'Hello.',
      ],
    ];

    $scenarios['with cache tag'] = [
      $basic_build + [
        '#cache' => [
          'tags' => ['test_tag'],
        ],
      ],
      'Hello.',
    ];

    $scenarios['with full cache info'] = [
      $basic_build + [
        '#cache' => [
          'tags' => ['test_tag', 'test_tag_2'],
          'contexts' => ['url'],
          'max-age' => 30,
        ],
      ],
      'Hello.',
    ];

    $scenarios['with attachments'] = [
      $basic_build + [
        '#attached' => [
          'library' => [
            'core/drupal',
            'core/once',
          ],
          'html_head_link' => ['test' => 'head'],
        ],
      ],
      'Hello.',
    ];

    $scenarios['with attachments and cache'] = [
      $basic_build + [
        '#attached' => [
          'library' => [
            'core/drupal',
            'core/once',
          ],
          'html_head_link' => ['test' => 'head'],
        ],
        '#cache' => [
          'contexts' => ['url.path'],
        ],
      ],
      'Hello.',
    ];

    $multiple_items_build = [
      [
        '#plain_text' => 'Item 1.',
      ],
      [
        '#plain_text' => 'Item 2.',
      ],
    ];
    $scenarios['multiple items'] = [
      $multiple_items_build,
      'Item 1.Item 2.',
    ];

    $scenarios['multiple items with metadata'] = [
      $multiple_items_build + [
        '#cache' => [
          'max-age' => CacheBackendInterface::CACHE_PERMANENT,
        ],
      ],
      'Item 1.Item 2.',
    ];

    // Theme wrappers or theme functions are ignored. Only bubbleable metadata
    // is kept.
    $scenarios['with theme wrappers'] = [
      $multiple_items_build + [
        '#theme_wrappers' => ['container'],
      ],
      'Item 1.Item 2.',
    ];

    $scenarios['sorting applied by default'] = [
      [
        [
          '#plain_text' => 'Weight 20.',
          '#weight' => 20,
        ],
        [
          '#plain_text' => 'Weight -3.',
          '#weight' => -3,
        ],
      ],
      'Weight -3.Weight 20.',
    ];

    return $scenarios;
  }

  /**
   * Tests BCL card list filter.
   */
  public function testBclCardList(): void {
    $extension = $this->container->get('oe_bootstrap_theme_helper.twig_extension');
    $data = $this->bclCardListDataProvider();
    $result = $extension->bclCardList($data['items']);

    foreach ($data['expected'] as $key => $expected) {
      $title = $result[$key]['title'] ?? NULL;
      $this->assertEquals($expected['title'], $title ? $title->getText() : '');
      $url = $title ? $title->getUrl() : NULL;

      if (isset($expected['url'])) {
        if ($url->isRouted()) {
          $this->assertEquals($expected['url'], $url->getRouteName());
        }
        else {
          $this->assertEquals($expected['url'], $url->getUri());
        }
        $this->assertEquals($expected['options'], $url->getOptions());
      }

      $this->assertEquals($expected['subtitle'], $result[$key]['subtitle'] ?? []);
      $this->assertEquals($expected['text'], $result[$key]['text'] ?? []);
      $this->assertEquals($expected['image'], $result[$key]['image'] ?? []);
      $this->assertEquals($expected['badges'], $result[$key]['badges'] ?? []);
    }
  }

  /**
   * Provides data for testBclCardList().
   *
   * @return array
   *   The scenarios.
   */
  public function bclCardListDataProvider(): array {
    return [
      'items' => [
        [
          'title' => 'Test title',
          'subtitle' => 'Test subtitle',
          'url' => Url::fromUserInput('/test#something'),
        ],
        [
          'title' => 'Test title',
          'url' => Url::fromRoute('<front>'),
          'text' => 'this is a text',
        ],
        [
          'title' => 'Some title',
          'url' => Url::fromUserInput('/test?foo=1&baz=2'),
        ],
        [
          'image' => ImageValueObject::fromArray([
            'src' => 'http://localhost:8080/web/sites/default/files/testimage.png',
            'name' => 'testimage',
          ]),
          'badges' => [
            'meta 1',
            'meta 2',
          ],
        ],
      ],
      'expected' => [
        [
          'title' => 'Test title',
          'subtitle' => [
            'content' => 'Test subtitle',
            'classes' => 'mb-2',
          ],
          'url' => 'base:test',
          'options' => [
            'attributes' => [
              'class' => 'standalone',
            ],
            'fragment' => 'something',
          ],
          'text' => [],
          'image' => [],
          'badges' => [],
        ],
        [
          'title' => 'Test title',
          'subtitle' => [],
          'url' => '<front>',
          'options' => [
            'attributes' => [
              'class' => 'standalone',
            ],
          ],
          'text' => [
            'content' => 'this is a text',
            'classes' => 'mb-2',
            'tag' => 'div',
          ],
          'image' => [],
          'badges' => [],
        ],
        [
          'title' => 'Some title',
          'subtitle' => [],
          'url' => 'base:test',
          'options' => [
            'attributes' => [
              'class' => 'standalone',
            ],
            'query' => [
              'foo' => 1,
              'baz' => 2,
            ],
          ],
          'text' => [],
          'image' => [],
          'badges' => [],
        ],
        [
          'title' => '',
          'subtitle' => [],
          'text' => [],
          'image' => [
            'path' => 'http://localhost:8080/web/sites/default/files/testimage.png',
            'alt' => '',
          ],
          'badges' => [
            [
              'label' => 'meta 1',
              'background' => 'primary',
            ],
            [
              'label' => 'meta 2',
              'background' => 'primary',
            ],
          ],
        ],
      ],
    ];
  }

}
