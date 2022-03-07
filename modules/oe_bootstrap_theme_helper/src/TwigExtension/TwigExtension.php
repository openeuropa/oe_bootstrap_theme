<?php

declare(strict_types = 1);

namespace Drupal\oe_bootstrap_theme_helper\TwigExtension;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Link;
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
   * Twig filter callback for 'bcl_card_list'.
   *
   * @param array $items
   *   Lists of items, as sent to e.g. 'card_layout' pattern.
   *
   * @return array
   *   Converted items, where each item is suitable for bcl-card.html.twig.
   */
  public function bclCardList(array $items): array {
    $bcl_cards = [];
    foreach ($items as $item) {
      // Copy most of the fields.
      $bcl_card = $item;
      // Some fields need to be rewritten.
      if (!empty($item['url'])) {
        /** @var \Drupal\Core\Url $url */
        $url = $item['url'];
        $url->setOptions(['attributes' => ['class' => 'text-underline-hover']]);
      }
      if (isset($item['title'])) {
        $title = isset($url) ? Link::fromTextAndUrl($item['title'], $url) : $item['title'];
        $bcl_card['title'] = [
          'content' => $title,
        ];
      }
      if (isset($item['subtitle'])) {
        $bcl_card['subtitle'] = [
          'content' => $item['subtitle'],
        ];
      }
      if (isset($item['text'])) {
        $bcl_card['text'] = [
          'content' => $item['text'],
          'classes' => 'mb-2',
          'tag' => 'div',
        ];
      }
      $bcl_card['badges'] = [];
      foreach ($item['badges'] ?? [] as $badge) {
        $bcl_card['badges'][] = [
          'label' => $badge,
          'background' => 'primary',
        ];
      }
      $bcl_cards[] = $bcl_card;
    }

    return $bcl_cards;
  }

}
