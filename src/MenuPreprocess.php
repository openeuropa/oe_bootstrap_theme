<?php

declare(strict_types = 1);

namespace Drupal\oe_bootstrap_theme;

use Drupal\Core\Template\Attribute;

/**
 * Utility class to process menu items.
 *
 * @internal
 */
class MenuPreprocess {

  /**
   * Adapts a menu link to BCL requirements.
   *
   * @param array $menu_link
   *   The menu link to alter.
   * @param array $extra_classes
   *   Extra classes for the link.
   *
   * @return array
   *   The altered menu link.
   */
  public function bclMenuLink(array $menu_link, array $extra_classes = []): array {
    $link = $menu_link + [
      'label' => $menu_link['title'],
      'path' => $menu_link['url'],
    ];
    $attributes = $menu_link['attributes'] ?? new Attribute();
    $attributes->addClass($extra_classes);

    if (!empty($menu_link['in_active_trail'])) {
      $attributes->addClass('active');
    }

    $link['attributes'] = $attributes;

    return $link;
  }

  /**
   * Prepare a list of local task menu links.
   *
   * @param array $local_tasks
   *   The local tasks.
   *
   * @return array
   *   A list of filtered and processed links.
   */
  public function prepareLocalTasks(array $local_tasks): array {
    $links = [];
    foreach ($local_tasks as $link) {
      if ($link['#access']->isForbidden()) {
        continue;
      }
      if ($link['#active']) {
        $link['#link']['in_active_trail'] = TRUE;
      }
      $links[] = $this->bclMenuLink($link['#link']);
    }

    return $links;
  }

}
