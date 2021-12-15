<?php

declare(strict_types = 1);

namespace Drupal\oe_bootstrap_theme_helper\TwigExtension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Collection of extra Twig extensions as filters.
 *
 * We don't enforce any strict type checking on filters' arguments as they are
 * coming straight from Twig templates.
 */
class TwigExtension extends AbstractExtension {

  /**
   * {@inheritdoc}
   */
  public function getFilters(): array {
    return [
      new TwigFilter('bcl_merge_icon', [$this, 'bclMergeIcon']),
    ];
  }

  /**
   * Filter gets an array of items adding size and path to icon elements.
   *
   * @param array $items
   *   The items that contains icons containing only names.
   * @param string $size
   *   The size of the icons.
   * @param string $path
   *   The path of the icons.
   *
   * @return array
   *   The array of items with icons elements having size and path.
   */
  public function bclMergeIcon(array $items, string $size = NULL, string $path = NULL): array {
    $items_rearranged = $items;

    if (empty($size) || empty($path)) {
      return $items_rearranged;
    }

    return $this->loop($items_rearranged, $size, $path);
  }

  /**
   * Gets an array and iterates through it searching for 'icon' elements.
   *
   * @param array $items
   *   The items that contains icons containing only names.
   * @param string $size
   *   The size of the icons.
   * @param string $path
   *   The path of the icons.
   *
   * @return array
   *   The array of items with icons elements having size and path.
   */
  private function loop(array $items, string $size, string $path): array {
    foreach ($items as $key => $value) {
      if ($key === 'icon') {
        $items[$key] = array_merge($value, ['size' => $size, 'path' => $path]);
      }
      elseif (is_array($value)) {
        $items[$key] = $this->loop($value, $size, $path);
      }
    }
    return $items;
  }

}
