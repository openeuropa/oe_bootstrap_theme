<?php

declare(strict_types = 1);

namespace Drupal\oe_bootstrap_theme_paragraphs\EventSubscriber;

use Drupal\oe_paragraphs\Event\IconOptionsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Provides options for the icon field.
 */
class IconOptionsSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      IconOptionsEvent::class => ['getIconOptions', -1],
    ];
  }

  /**
   * Gets the icon options.
   *
   * @param \Drupal\custom_events\Event\IconOptionsEvent $event
   *   Allowed format event object.
   */
  public function getIconOptions(IconOptionsEvent $event): void {
    $event->setIconOptions([
      'arrow-down' => 'Arrow down',
      'box-arrow-up' => 'External',
      'arrow-up' => 'Arrow up',
      'book' => 'Book',
      'camera' => 'Camera',
      'check' => 'Check',
      'download' => 'Download',
      'currency-euro' => 'Euro',
      'facebook' => 'Facebook',
      'file' => 'File',
      'image' => 'Image',
      'info' => 'Info',
      'linkedin' => 'LinkedIn',
      'files' => 'Multiple files',
      'rss' => 'RSS',
      'search' => 'Search',
      'share' => 'Share',
      'twitter' => 'Twitter',
      'person-video' => 'Video',
    ]);
  }

}
