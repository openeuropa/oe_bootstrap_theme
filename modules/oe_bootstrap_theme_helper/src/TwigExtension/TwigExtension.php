<?php

declare(strict_types = 1);

namespace Drupal\oe_bootstrap_theme_helper\TwigExtension;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Collection of extra Twig extensions as filters and functions.
 */
class TwigExtension extends AbstractExtension {

  /**
   * The plugin.manager.block service.
   *
   * @var \Drupal\Core\Cache\CacheableDependencyInterface
   */
  protected $pluginManagerBlock;

  /**
   * Constructs the TwigExtension object.
   */
  public function __construct(CacheableDependencyInterface $plugin_manager_block) {
    $this->pluginManagerBlock = $plugin_manager_block;
  }

  /**
   * {@inheritdoc}
   */
  public function getFilters(): array {
    return [
      new TwigFilter('bcl_card_list', [$this, 'bclCardList']),
    ];
  }

  /**
   * Filters a list of cards.
   *
   * @param array $items
   *   Datetime to be parsed.
   *
   * @return array
   *   The translated time ago string.
   */
  public function bclCardList(array $items): array {
    $cardItems = [];
    foreach ($items as &$item) {
      if (isset($item['title'])) {
        $title = $item['title'];
        $item['title'] = [
          'content' => $title,
        ];
      }
      if (isset($item['subtitle'])) {
        $subtitle = $item['subtitle'];
        $item['subtitle'] = [
          'content' => $subtitle,
        ];
      }
      if (isset($item['text'])) {
        $text = $item['text'];
        $item['text'] = [
          'content' => $text,
          'classes' => 'mb-2',
          'tag' => 'div',
        ];
      }
      if (isset($item['badges'])) {
        $metas = [];
        foreach ($item['badges'] as $item_meta) {
          $metas[] = [
            'label' => $item_meta,
            'background' => 'primary',
          ];
        }
        $item['badges'] = $metas;
      }

      $cardItems[] = $item;
    }

    return $cardItems;
  }

  /**
   * Processes footer links to make them compatible with BCL formatting.
   *
   * @param array $context
   *   The twig context.
   * @param array $links
   *   Set of links to be processed.
   *
   * @return array
   *   Set of processed links.
   */
  public function bclFooterLinks(array $context, array $links): array {
    $altered_links = [];

    foreach ($links as $link) {
      $altered_link = [
        'label' => $link['label'],
        'path' => $link['href'],
        'icon_position' => 'after',
        'standalone' => TRUE,
        'attributes' => [
          'class' => [
            'd-block',
            'mb-1',
          ],
        ],
      ];

      if (!empty($link['external']) && $link['external'] === TRUE) {
        $altered_link['icon'] = [
          'path' => $context['bcl_icon_path'],
          'name' => 'box-arrow-up-right',
          'size' => 'xs',
        ];
      }

      if (!empty($link['social_network'])) {
        $altered_link['icon_position'] = 'before';
        $altered_link['icon'] = [
          'path' => $context['bcl_icon_path'],
          'name' => $link['social_network'],
          'size' => 'xs',
        ];
      }

      $altered_links[] = $altered_link;
    }

    return $altered_links;
  }

  /**
   * Builds the render array for a block.
   *
   * @param string $id
   *   The block plugin ID.
   * @param array $configuration
   *   The block configuration.
   *
   * @return array
   *   The block render array.
   */
  public function bclBlock(string $id, array $configuration = []): array {
    $configuration += ['label_display' => 'hidden'];

    /** @var \Drupal\Core\Block\BlockPluginInterface $block_plugin */
    $block_plugin = $this->pluginManagerBlock->createInstance($id, $configuration);

    return $block_plugin->build();
  }

}
