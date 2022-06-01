<?php

declare(strict_types = 1);

namespace Drupal\oe_bootstrap_theme_helper\TwigExtension;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\RendererInterface;
use Drupal\oe_bootstrap_theme_helper\EuropeanUnionLanguages;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Collection of extra Twig extensions as filters and functions.
 */
class TwigExtension extends AbstractExtension {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a new TwigExtension object.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(LanguageManagerInterface $languageManager, RendererInterface $renderer) {
    $this->languageManager = $languageManager;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public function getFilters(): array {
    return [
      new TwigFilter('bcl_card_list', [$this, 'bclCardList']),
      new TwigFilter('format_size', 'format_size'),
      new TwigFilter('to_file_icon', [$this, 'toFileIcon']),
      new TwigFilter('to_native_language', [
        $this,
        'toNativeLanguageName',
      ]),
      new TwigFilter('to_internal_language_id', [
        $this,
        'toInternalLanguageId',
      ]),
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
        $bcl_card['title'] = $title;
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
      if (isset($item['image'])) {
        $bcl_card['image'] = [
          'path' => $item['image']->getSource(),
          'alt' => $item['image']->getAlt(),
        ];
      }

      $bcl_cards[] = $bcl_card;
    }

    return $bcl_cards;
  }

  /**
   * Get file icon class given its extension.
   *
   * @param string $extension
   *   File extension.
   *
   * @return string
   *   File icon class name.
   */
  public function toFileIcon(string $extension): string {
    $extension = strtolower($extension);
    $extension_mapping = [
      'image' => [
        'jpg',
        'jpeg',
        'gif',
        'png',
        'webp',
      ],
      'presentation' => [
        'ppt',
        'pptx',
        'pps',
        'ppsx',
        'odp',
      ],
      'file-excel-fill' => [
        'xls',
        'xlsx',
        'ods',
      ],
      'video' => [
        'mp4',
        'mp4',
        'mpeg',
        'avi',
        'm4v',
        'webm',
      ],
      'file-text-fill' => [
        'txt',
      ],
      'file-pdf-fill' => [
        'pdf',
        'application/pdf',
      ],
      'file-word-fill' => [
        'word',
        'odt',
      ],
    ];

    foreach ($extension_mapping as $icon => $extensions) {
      if (in_array($extension, $extensions)) {
        return $icon;
      }
    }

    return 'file-earmark';
  }

  /**
   * Get a native language name given its code.
   *
   * @param string $language_code
   *   The language code as defined by the W3C language tags document.
   *
   * @return string
   *   The native language name.
   *
   * @throws \InvalidArgumentException
   *   Thrown when the passed in language code does not exist.
   */
  public function toNativeLanguageName($language_code): string {
    $languages = $this->languageManager->getNativeLanguages();
    if (!empty($languages[$language_code])) {
      return $languages[$language_code]->getName();
    }
    // The fallback implemented in case we don't have enabled language.
    $predefined = EuropeanUnionLanguages::getLanguageList() + $this->languageManager::getStandardLanguageList();
    if (!empty($predefined[$language_code][1])) {
      return $predefined[$language_code][1];
    }

    throw new \InvalidArgumentException('The language code ' . $language_code . ' does not exist.');
  }

  /**
   * Get an internal language ID given its code.
   *
   * @param string $language_code
   *   The language code as defined by the W3C language tags document.
   *
   * @return string
   *   The internal language ID, or the given language code if none found.
   */
  public function toInternalLanguageId(string $language_code): string {
    if (EuropeanUnionLanguages::hasLanguage($language_code)) {
      return EuropeanUnionLanguages::getInternalLanguageCode($language_code);
    }

    return $language_code;
  }

}
