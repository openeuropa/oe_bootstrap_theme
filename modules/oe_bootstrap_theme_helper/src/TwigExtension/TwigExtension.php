<?php

declare(strict_types = 1);

namespace Drupal\oe_bootstrap_theme_helper\TwigExtension;

use Drupal\Component\Utility\Html;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\Markup;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Template\TwigEnvironment;
use Drupal\Core\Template\TwigExtension as CoreTwigExtension;
use Drupal\Core\Url;
use Drupal\oe_bootstrap_theme_helper\EuropeanUnionLanguages;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Markup as TwigMarkup;
use Twig\TwigFilter;
use Twig\TwigFunction;

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
   * The Drupal Twig environment.
   *
   * @var \Drupal\Core\Template\TwigEnvironment
   */
  protected TwigEnvironment $twigEnvironment;

  /**
   * Constructs a new TwigExtension object.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   * @param \Drupal\Core\Template\TwigEnvironment $twigEnvironment
   *   The Drupal Twig environment.
   */
  public function __construct(LanguageManagerInterface $languageManager, TwigEnvironment $twigEnvironment) {
    $this->languageManager = $languageManager;
    $this->twigEnvironment = $twigEnvironment;
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
   * {@inheritdoc}
   */
  public function getFunctions(): array {
    return [
      new TwigFunction('bcl_link', [$this, 'bclLink'], [
        'needs_environment' => TRUE,
      ]),
      new TwigFunction('bcl_gallery_items', [$this, 'bclGalleryItems']),
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
        // Allow to specify the item url in a separate key, as an Url object.
        // Unfortunately this cannot be covered in yml-based tests and previews.
        // Alternatively, the 'title' key can already contain a rendered link,
        // but then the calling template must add the 'text-underline-hover'
        // class.
        if (!empty($item['url']) && $item['url'] instanceof Url) {
          // Use clone, to not pollute the original object with attributes.
          $url = clone $item['url'];
          $url->setOptions(['attributes' => ['class' => 'standalone']]);
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
          'content' => Markup::create($item['text']),
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

  /**
   * Alter a link with BCL logic.
   *
   * @param \Twig\Environment $env
   *   The env.
   * @param string $label
   *   The link text for the anchor tag as a translated string.
   * @param \Drupal\Core\Url|string $path
   *   The URL object or string used for the link.
   * @param array|\Drupal\Core\Template\Attribute $attributes
   *   An optional array or Attribute object of link attributes.
   *
   * @return array
   *   The link render array.
   */
  public function bclLink(Environment $env, $label, $path, Attribute $attributes): array {
    if (is_string($path)) {
      // The text has been processed by twig already, convert it to a safe
      // object for the render system.
      if ($label instanceof TwigMarkup) {
        $label = Markup::create($label);
      }
      return [
        '#type' => 'html_tag',
        '#tag' => 'a',
        '#value' => $label,
        '#attributes' => $attributes->setAttribute('href', $path),
      ];
    }

    return $env->getExtension(CoreTwigExtension::class)->getLink($label, $path, $attributes);
  }

  /**
   * Processes the items for the gallery pattern.
   *
   * @param array $items
   *   The gallery items.
   *
   * @return array
   *   The process gallery items.
   */
  public function bclGalleryItems(array $items): array {
    foreach ($items as &$item) {
      // Use inline templates to take care of all possible types at once, and to
      // receive a Markup class as output.
      $rendered = $this->twigEnvironment->renderInline('{{ value }}', [
        'value' => $item['media'],
      ]);
      $document = Html::load((string) $rendered);
      $xpath = new \DOMXPath($document);
      $is_iframe = (bool) $xpath->query('//iframe')->count();
      $is_video = (bool) $xpath->query('//video')->count();
      $is_image = (bool) $xpath->query('//img')->count();

      // Playable medias show an icon.
      $item['is_playable'] = $is_iframe || $is_video;

      if (!$is_iframe && !$is_image) {
        $item['image'] = $item['media'];
        continue;
      }

      $item['image'] = Markup::create(str_replace(' src=', ' data-src=', (string) $rendered));
    }

    return $items;
  }

}
