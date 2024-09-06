<?php

declare(strict_types=1);

namespace Drupal\oe_bootstrap_theme;

use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Template\Attribute;

/**
 * Class containing menu theme and preprocesses.
 */
class MenuPreprocess {

  /**
   * Implements hook_preprocess_menu().
   *
   * Transforms the data structure for the navigation pattern.
   *
   * @param array $variables
   *   The variables array.
   * @param string $hook
   *   The preprocess hook.
   */
  public function preprocessMenu(array &$variables, string $hook): void {
    if (in_array($hook, ['menu__toolbar', 'navigation_menu'])) {
      return;
    }

    foreach ($variables['items'] as &$item) {
      $item = $this->menuLink($item, ['nav-link']);

      if (empty($item['below'])) {
        continue;
      }

      foreach ($item['below'] as &$sub_item) {
        $sub_item = $this->menuLink($sub_item);
      }

      $item = [
        'standalone' => TRUE,
        'dropdown' => TRUE,
        'link' => TRUE,
        'trigger' => $item,
        'items' => $item['below'],
      ];
    }
  }

  /**
   * Implements template_preprocess_menu_local_action().
   *
   * Themes the local actions as BCL buttons.
   *
   * @param array $variables
   *   The variables array.
   */
  public function preprocessMenuLocalAction(array &$variables): void {
    $variables['link']['#options']['attributes']['class'][] = 'btn';
    $variables['link']['#options']['attributes']['class'][] = 'btn-sm';
    $variables['link']['#options']['attributes']['class'][] = 'btn-primary';
  }

  /**
   * Implements template_preprocess_links__dropbutton().
   *
   * Themes the dropbutton links as BCL buttons.
   *
   * @param array $variables
   *   The variables array.
   */
  public function preprocessLinksDropbutton(array &$variables): void {
    $links = &$variables['links'];

    // Do nothing if we have no links.
    if (!count($links)) {
      return;
    }

    // Get the first link and use it for the dropbutton.
    $link = reset($links);

    /** @var \Drupal\Core\Url $url */
    $variables['split'] = FALSE;
    $button = $link['text'];
    if (isset($link['link']) && ($url = $link['link']['#url'])) {
      $button = $link['link'];

      if ($url->getRouteName() !== '<nolink>') {
        $variables['split'] = TRUE;
        $button['#options']['attributes']['class'][] = 'btn';
        $button['#options']['attributes']['class'][] = 'btn-sm';
        $button['#options']['attributes']['class'][] = 'btn-outline-primary';
      }

      // Remove first link from links.
      array_shift($links);
    }

    $variables['button'] = $button;
    $this->addDropbuttonClasses($links);
  }

  /**
   * Implements hook_preprocess_menu_local_tasks().
   *
   * Preprocess local tasks as BCL menu links.
   *
   * @param array $variables
   *   The variables array.
   */
  public function preprocessMenuLocalTasks(array &$variables): void {
    // Sort local tasks by weight.
    uasort($variables['primary'], [
      '\Drupal\Component\Utility\SortArray',
      'sortByWeightProperty',
    ]);
    uasort($variables['secondary'], [
      '\Drupal\Component\Utility\SortArray',
      'sortByWeightProperty',
    ]);

    $variables['primary'] = $this->prepareLocalTasks($variables['primary']);
    $variables['secondary'] = $this->prepareLocalTasks($variables['secondary']);
  }

  /**
   * Adds required classes to dropbutton links.
   *
   * @param array $links
   *   Array with links items.
   */
  public function addDropbuttonClasses(array &$links): void {
    foreach ($links as $key => $link) {
      if (isset($links[$key]['link']) && is_array($links[$key]['link'])) {
        $links[$key]['link']['#options']['attributes']['class'][] = 'dropdown-item';
      }
      if (isset($links[$key]['text_attributes'])) {
        $links[$key]['text_attributes']->addClass('dropdown-item');
      }
      if (isset($links[$key]['attributes'])) {
        $links[$key]['attributes']->addClass('dropdown-item');
      }
      if (isset($links[$key]['text']) && is_array($links[$key]['text'])) {
        $links[$key]['text']['#attributes']['class'][] = 'dropdown-item';
      }
    }
  }

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
  public function menuLink(array $menu_link, array $extra_classes = []): array {
    $link = $menu_link + [
      'label' => $menu_link['title'],
      'path' => $menu_link['url'],
    ];

    $attributes = [];
    if (!empty($menu_link['attributes'])) {
      $attributes = $menu_link['attributes'] instanceof Attribute ? $menu_link['attributes']->toArray() : $menu_link['attributes'];
    }
    $class = $attributes['class'] ?? [];
    $class = array_merge($class, $extra_classes);

    if (!empty($menu_link['in_active_trail'])) {
      $class[] = 'active';
    }

    $attributes['class'] = $class;
    $link['attributes'] = new Attribute($attributes);

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
      // @see \Drupal\Core\Render\Element::isVisibleElement()
      $access = (!isset($link['#access'])
        || (($link['#access'] instanceof AccessResultInterface && $link['#access']->isAllowed()) || ($link['#access'] === TRUE)));
      if (!$access) {
        continue;
      }

      if ($link['#active']) {
        $link['#link']['in_active_trail'] = TRUE;
      }
      $links[] = $this->menuLink($link['#link']);
    }

    return $links;
  }

}
