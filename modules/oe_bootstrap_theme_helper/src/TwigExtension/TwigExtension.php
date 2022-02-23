<?php

declare(strict_types = 1);

namespace Drupal\oe_bootstrap_theme_helper\TwigExtension;

use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\oe_bootstrap_theme_helper\EuropeanUnionLanguages;
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
    $predefined = EuropeanUnionLanguages::getLanguageList() + LanguageManager::getStandardLanguageList();
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
  public function toInternalLanguageId($language_code): string {
    if (EuropeanUnionLanguages::hasLanguage($language_code)) {
      return EuropeanUnionLanguages::getInternalLanguageCode($language_code);
    }

    return $language_code;
  }

}
