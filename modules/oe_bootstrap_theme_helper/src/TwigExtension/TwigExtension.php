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
   * Maps cards items.
   *
   * @param array $items
   *   Lists of items.
   *
   * @return array
   *   Items mapped as a card.
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

}
