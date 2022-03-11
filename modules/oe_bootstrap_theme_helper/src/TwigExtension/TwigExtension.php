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
   * This is meant to be called in pattern templates, to convert items from
   * pattern space to BCL component space.
   * Most of the conversion mimicks the logic from pattern-card.html.twig, but
   * there are a few differences.
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
      if (isset($item['title'])) {
        $title = $item['title'];
        // Allow to specify the item url in a separate key, as a Url object.
        // Unfortunately this cannot be covered in yml-based tests and previews.
        // Alternatively, the 'title' key can already contain a rendered link,
        // but then the calling template must add the 'text-underline-hover'
        // class.
        if (!empty($item['url'])) {
          // Use clone, to not pollute the original object with attributes.
          /** @var \Drupal\Core\Url $url */
          $url = clone $item['url'];
          $url->setOptions(['attributes' => ['class' => 'text-underline-hover']]);
          $title = Link::fromTextAndUrl($title, $url);
        }
        $bcl_card['title'] = [
          'content' => $title,
        ];
      }
      if (isset($item['subtitle'])) {
        $bcl_card['subtitle'] = [
          'content' => $item['subtitle'],
          'classes' => 'mb-2',
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
