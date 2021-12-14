<?php

declare(strict_types = 1);

namespace Drupal\oe_bootstrap_theme_helper\TwigExtension;

use Drupal\Core\StringTranslation\PluralTranslatableMarkup;
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
      new TwigFilter('bcl_timeago', [$this, 'bclTimeAgo']),
    ];
  }

  /**
   * Filters a timestamp in "time ago" format.
   *
   * @param string $timestamp
   *   Datetime to be parsed.
   *
   * @return \Drupal\Core\StringTranslation\PluralTranslatableMarkup
   *   The translated time ago string.
   */
  public function bclTimeAgo(string $timestamp): PluralTranslatableMarkup {
    $time = \Drupal::time()->getCurrentTime() - $timestamp;
    $time_ago = new PluralTranslatableMarkup(0, 'N/A', 'N/A');
    $units = [
      31536000 => [
        'singular' => '@number year ago',
        'plural' => '@number years ago',
      ],
      2592000 => [
        'singular' => '@number month ago',
        'plural' => '@number months ago',
      ],
      604800 => [
        'singular' => '@number week ago',
        'plural' => '@number weeks ago',
      ],
      86400 => [
        'singular' => '@number day ago',
        'plural' => '@number days ago',
      ],
      3600 => [
        'singular' => '@number hour ago',
        'plural' => '@number hours ago',
      ],
      60 => [
        'singular' => '@number minute ago',
        'plural' => '@number minutes ago',
      ],
      1 => [
        'singular' => '@number second ago',
        'plural' => '@number seconds ago',
      ],
    ];

    foreach ($units as $unit => $format) {
      if ($time < $unit) {
        continue;
      }

      $number_of_units = floor($time / $unit);
      $time_ago = \Drupal::translation()
        ->formatPlural($number_of_units, $format['singular'], $format['plural'], ['@number' => $number_of_units]);
      break;
    }

    return $time_ago;
  }

}
