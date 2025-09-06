<?php

declare(strict_types=1);

namespace Drupal\oe_bootstrap_theme_helper\Plugin\Block;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Block\Plugin\Block\Broken;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Url;
use Drupal\oe_bootstrap_theme_helper\Traits\PluginAutowireTrait;
use Drupal\system\MenuInterface;
use Drupal\system\Plugin\Block\SystemMenuBlock;

/**
 * Provides a mega menu block.
 *
 * @see \Drupal\system\Plugin\Block\SystemMenuBlock
 */
#[Block(
  'oel_mega_menu',
  new TranslatableMarkup('OEL Mega menu'),
  new TranslatableMarkup('Menus'),
)]
class MegaMenuBlock extends BlockBase implements ContainerFactoryPluginInterface {

  use PluginAutowireTrait;

  public function __construct(
    array $configuration,
    string $plugin_id,
    array $plugin_definition,
    protected readonly BlockManagerInterface $blockManager,
    protected readonly EntityTypeManagerInterface $entityTypeManager,
    TranslationInterface $translation,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->setStringTranslation($translation);
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies(): array {
    // The current version of SystemMenuBlock does not specify dependencies, but
    // future versions might.
    $dependencies = $this->getSystemMenuBlock()?->calculateDependencies() ?? [];
    $menu_id = $this->configuration['menu'] ?? NULL;
    if ($menu_id) {
      $menu_entity = $this->entityTypeManager
        ->getStorage('menu')
        ->load($menu_id);
      if ($menu_entity !== NULL) {
        assert($menu_entity instanceof MenuInterface);
        $dependencies[$menu_entity->getConfigDependencyKey()][] = $menu_entity->getConfigDependencyName();
      }
    }

    return $dependencies;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return $this->getSystemMenuBlock()?->getCacheTags() ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return $this->getSystemMenuBlock()?->getCacheContexts() ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $system_menu_block = $this->getSystemMenuBlock();
    if ($system_menu_block === NULL) {
      return [];
    }
    $build = $system_menu_block->build();
    $bcl_items = array_map(
      fn ($item) => $this->buildBclItem($item, 0),
      $build['#items'] ?? [],
    );
    if (!$bcl_items) {
      return $build;
    }
    return [
      '#type' => 'pattern',
      '#id' => 'navigation',
      '#variant' => 'navbar',
      '#fields' => [
        'items' => $bcl_items,
      ],
      '#cache' => $build['#cache'],
    ];
  }

  /**
   * Gets a SystemMenuBlock instance with similar settings as this plugin.
   *
   * @return \Drupal\system\Plugin\Block\SystemMenuBlock|null
   *   The system menu block instance, or NULL if configuration is incomplete.
   */
  protected function getSystemMenuBlock(): ?SystemMenuBlock {
    $menu_name = $this->configuration['menu'] ?? NULL;
    if (!$menu_name) {
      return NULL;
    }
    $system_menu_block = $this->blockManager->createInstance(
      'system_menu_block:' . $menu_name,
      [
        'depth' => 3,
        'expand_all_items' => TRUE,
      ],
    );
    if ($system_menu_block instanceof Broken) {
      // The menu does not exist.
      // The block manager already logs this, so it is ok to simply return NULL.
      return NULL;
    }
    assert($system_menu_block instanceof SystemMenuBlock);
    return $system_menu_block;
  }

  /**
   * Build an item suitable for the pattern / BCL template.
   *
   * @param array $item
   *   Item from $build['#items'] as from the regular SystemMenuBlock.
   * @param int $level
   *   Current submenu level, starting at zero.
   *
   * @return array
   *   Item suitable for the BCL template.
   */
  protected function buildBclItem(array $item, int $level): array {
    $url = $item['url'];
    assert($url instanceof Url);
    $bcl_link = [
      'label' => $item['title'],
      'path' => $url,
      'attributes' => $this->buildLinkAttributes($item, $level),
    ];
    $is_nolink = $url->isRouted() && $url->getRouteName() === '<nolink>';
    if (empty($item['below']) || $level > 1) {
      return $bcl_link;
    }
    $bcl_item = [
      'trigger' => [
        'label' => $bcl_link['label'],
        'attributes' => $bcl_link['attributes'],
      ],
      'items' => array_map(
        fn ($item) => $this->buildBclItem($item, $level + 1),
        $item['below'],
      ),
    ];
    if ($level === 0) {
      $bcl_item += [
        'mega_menu' => TRUE,
        'content_block' => $this->buildContentBlock($item),
      ];
      if (!$is_nolink) {
        // Remove the link title attribute, it is now idenical with the
        // description.
        $more_url = clone $url;
        assert($more_url instanceof Url);
        $attributes = $more_url->getOption('attributes');
        if (is_array($attributes)) {
          unset($attributes['title']);
          $more_url->setOption('attributes', $attributes);
        }
        $bcl_item['content_link'] = [
          'label' => $this->t('Discover more'),
          'path' => $more_url,
        ];
      }
    }
    elseif (!$is_nolink) {
      $bcl_item['see_all'] = [
        'label' => $this->t('More of @title', ['@title' => mb_lcfirst($item['title'])]),
        'path' => $url,
      ];
    }
    return $bcl_item;
  }

  /**
   * Builds link attributes.
   *
   * @param array $item
   *   Item as from $build['#items'].
   * @param int $level
   *   Current submenu level, starting at zero.
   *
   * @return \Drupal\Core\Template\Attribute
   *   Attributes for the link element.
   */
  protected function buildLinkAttributes(array $item, int $level): Attribute {
    $extra_classes = ($level === 0) ? ['nav-link'] : [];
    $attributes = $item['attributes'] ?? [];
    if (!$attributes instanceof Attribute) {
      $attributes = new Attribute($attributes);
    }
    $attributes->addClass($extra_classes);
    if (!empty($item['in_active_trail'])) {
      $attributes->addClass('active');
    }
    return $attributes;
  }

  /**
   * Builds content for the first column of the megamenu.
   *
   * @param array $item
   *   The top-level item.
   *
   * @return array
   *   Render element for the content area in the left column of the mega menu.
   */
  protected function buildContentBlock(array $item): array {
    $content_block = [
      'title' => [
        '#type' => 'html_tag',
        '#tag' => 'h4',
        '#value' => $item['title'],
      ],
    ];
    $description = $this->extractItemDescription($item);
    if (trim($description ?? '') !== '') {
      $content_block['text'] = [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $description,
      ];
    }
    return $content_block;
  }

  /**
   * Gets the description from a menu item.
   *
   * @param array $item
   *   Menu item as from $build['#items'].
   *
   * @return string|null
   *   Item description, or NULL if item has no description.
   */
  protected function extractItemDescription(array $item): string|null {
    $url = $item['url'] ?? NULL;
    if (!$url instanceof Url) {
      return NULL;
    }
    $attributes = $url->getOption('attributes');
    $description = $attributes['title'] ?? NULL;
    if ($description !== NULL) {
      $description = Unicode::truncate($description, 170);
    }
    return $description;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state): array {
    $menu_options = $this->getMenuOptions();
    $form['menu'] = [
      '#type' => 'select',
      '#title' => $this->t('Menu'),
      '#options' => $menu_options,
      '#default_value' => $this->configuration['menu'] ?? NULL,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['menu'] = $form_state->getValue('menu');
  }

  /**
   * Gets a map of menu names to use as select options.
   *
   * @return array<string, string|\Drupal\Core\StringTranslation\TranslatableMarkup>
   *   Menu labels by menu id.
   */
  protected function getMenuOptions() {
    /** @var \Drupal\system\MenuInterface[] $menus */
    $menus = $this->entityTypeManager->getStorage('menu')->loadMultiple();
    $options = [];
    foreach ($menus as $menu) {
      $options[$menu->id()] = $menu->label() ?? $menu->id();
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $main_menu = $this->entityTypeManager->getStorage('menu')->load('main');
    return [
      'menu' => ($main_menu !== NULL) ? 'main' : NULL,
    ];
  }

}
