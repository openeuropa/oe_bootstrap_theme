<?php

declare(strict_types=1);

namespace Drupal\oe_bootstrap_theme_helper\TwigExtension;

use Drupal\Component\Utility\Html;
use Drupal\Core\Extension\ThemeExtensionList;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\Element;
use Drupal\Core\Render\Markup;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Template\TwigEnvironment;
use Drupal\Core\Template\TwigExtension as CoreTwigExtension;
use Drupal\Core\Url;
use Drupal\oe_bootstrap_theme\DrupalCompatibility;
use Drupal\oe_bootstrap_theme\ValueObject\FileValueObject;
use Drupal\oe_bootstrap_theme\ValueObject\ImageValueObject;
use Drupal\oe_bootstrap_theme\ValueObject\ImageValueObjectInterface;
use Drupal\oe_bootstrap_theme_helper\EuropeanUnionLanguages;
use Drupal\oe_bootstrap_theme_helper\Value\NamedParametersOnly;
use Twig\Attribute\AsTwigFilter;
use Twig\Attribute\AsTwigFunction;
use Twig\Environment;
use Twig\Error\RuntimeError;
use Twig\Extension\AbstractExtension;
use Twig\Markup as TwigMarkup;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Collection of extra Twig extensions as filters and functions.
 */
class TwigExtension extends AbstractExtension {

  public function __construct(
    protected readonly LanguageManagerInterface $languageManager,
    protected readonly TwigEnvironment $twigEnvironment,
    protected readonly RendererInterface $renderer,
    protected readonly ThemeExtensionList $themeList,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function getFilters(): array {
    $filters = [
      new TwigFilter('bcl_card_list', [$this, 'bclCardList']),
      new TwigFilter('format_size', 'Drupal\Core\StringTranslation\ByteSizeMarkup::create'),
      new TwigFilter('to_file_icon', [$this, 'toFileIcon']),
      new TwigFilter('to_native_language', [
        $this,
        'toNativeLanguageName',
      ]),
      new TwigFilter('to_internal_language_id', [
        $this,
        'toInternalLanguageId',
      ]),
      new TwigFilter('element_children', [$this, 'elementChildren']),
      new TwigFilter('to_image_value_object', [$this, 'toImageValueObject']),
      new TwigFilter('to_file_value_object', [$this, 'toFileValueObject']),
    ];
    // Use attributes for newly added twig filters.
    // @todo This likely needs to be updated once Drupal natively supports these
    //   Twig attributes.
    $reflection = new \ReflectionClass($this);
    foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
      if ($method->isAbstract() || $method->isStatic()) {
        continue;
      }
      foreach ($method->getAttributes(AsTwigFilter::class) as $reflection_attribute) {
        $attribute = $reflection_attribute->newInstance();
        assert($attribute instanceof AsTwigFilter);
        $filters[] = new TwigFilter($attribute->name, [$this, $method->name]);
      }
    }
    return $filters;
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions(): array {
    $functions = [
      new TwigFunction('bcl_link', [$this, 'bclLink'], [
        'needs_environment' => TRUE,
      ]),
      new TwigFunction('bcl_gallery_items', [$this, 'bclGalleryItems']),
      new TwigFunction('bcl_icon_path', [$this, 'getBclIconPath']),
      new TwigFunction('theme_setting', [DrupalCompatibility::class, 'themeGetSetting']),
    ];
    // Use attributes for newly added twig functions.
    // @todo This likely needs to be updated once Drupal natively supports these
    //   Twig attributes.
    $reflection = new \ReflectionClass($this);
    foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
      if ($method->isAbstract() || $method->isStatic()) {
        continue;
      }
      foreach ($method->getAttributes(AsTwigFunction::class) as $reflection_attribute) {
        $attribute = $reflection_attribute->newInstance();
        assert($attribute instanceof AsTwigFunction);
        $functions[] = new TwigFunction($attribute->name, [$this, $method->name]);
      }
    }
    return $functions;
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
          $url->mergeOptions(['attributes' => ['class' => 'standalone']]);
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

  /**
   * Alter a link with BCL logic.
   *
   * @param \Twig\Environment $env
   *   The env.
   * @param string $label
   *   The link text for the anchor tag as a translated string.
   * @param \Drupal\Core\Url|string $path
   *   The URL object or string used for the link.
   * @param \Drupal\Core\Template\Attribute $attributes
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
      elseif (is_array($label)) {
        // @see \Drupal\Core\Utility\LinkGenerator::generate()
        $label = $this->renderer->render($label);
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

  /**
   * Returns the URL to the BCL default icons SVG file.
   *
   * @return string
   *   The relative URL to the BCL icons SVG file.
   */
  public function getBclIconPath(): string {
    $theme = $this->themeList->get('oe_bootstrap_theme');
    return base_path() . $theme->getPath() . '/assets/icons/bcl-default-icons.svg';
  }

  /**
   * Returns the children of an element array, optionally sorted by weight.
   *
   * This method does not return the keys, but the elements themselves.
   * The method will make sure that bubbleable metadata is propagated.
   *
   * @param array $elements
   *   The element array whose children are to be identified. Passed by
   *    reference.
   * @param bool $sort
   *   Boolean to indicate whether the children should be sorted by weight.
   *   Defaults to TRUE to mimic Drupal renderer.
   *
   * @return array
   *   A new array with the element's children.
   */
  public function elementChildren(array $elements, bool $sort = TRUE): array {
    $children = [];

    foreach (Element::children($elements, $sort) as $key) {
      $children[$key] = $elements[$key];
    }

    // Make sure to bubble all cache and metadata information, like libraries.
    $bubbleable = [];
    BubbleableMetadata::createFromRenderArray($elements)->applyTo($bubbleable);
    $this->renderer->render($bubbleable);

    return $children;
  }

  /**
   * Prepares parameters for the 'bcl-icon' template.
   *
   * @param mixed $argument
   *   The input, which should be one of:
   *     - The icon name, as a string.
   *     - An array with icon name, and optionally path and size.
   *     - NULL or empty array.
   * @param mixed $_
   *   A placeholder parameter to begin named parameters.
   *   This parameter should never be passed, and all subsequent parameters
   *   should be passed by name only.
   * @param string|null $name
   *   A fallback/default for the icon name, identifying the icon within an svg
   *   file.
   * @param string|null $path
   *   A fallback/default for the path to the icons svg file.
   * @param string|null $size
   *   A fallback/default for the icon size.
   *   Example values are '2xs', 'xs', 'm', 'l', 'xl' etc.
   *
   * @return array{name: string, path?: string, size?: string}|null
   *   Arguments suitable for 'bcl-icon', or NULL for no icon.
   */
  #[AsTwigFilter('bcl_icon')]
  public function bclIcon(
    mixed $argument,
    mixed $_ = NamedParametersOnly::SENTINEL,
    ?string $name = NULL,
    ?string $path = NULL,
    ?string $size = NULL,
  ): ?array {
    NamedParametersOnly::checkForTwig($_);
    if ($argument === NULL || $argument === [] || $argument === '') {
      if ($name === NULL) {
        return NULL;
      }
      $icon = ['name' => $name];
    }
    elseif (is_string($argument)) {
      $icon = ['name' => $argument];
    }
    elseif (is_array($argument)) {
      if (!isset($argument['name'])) {
        if ($name === NULL) {
          throw new RuntimeError('Missing icon name in non-empty icon array.');
        }
        $argument['name'] = $name;
      }
      $icon = $argument;
    }
    else {
      throw new RuntimeError(sprintf('Expected an array, string or NULL, found %s', get_debug_type($argument)));
    }
    if (!empty($icon['attributes'])) {
      $icon['attributes'] = $this->toAttribute($icon['attributes']);
    }
    else {
      unset($icon['attributes']);
    }
    $icon['path'] ??= $path ?? $this->getBclIconPath();
    if ($size !== NULL) {
      $icon['size'] ??= $size;
    }
    return $icon;
  }

  /**
   * Populates the 'icon' array key for use in the 'bcl-icon' template.
   *
   * @param array|null $argument
   *   An associative array which may or may not have a [$key] key.
   *   If $argument[$key] is present, it should be either a string holding the
   *   icon name, or an array where ['name'] holds the icon name.
   * @param mixed $_
   *   Internal parameter used for named parameters validation.
   *   Must not be explicitly provided.
   * @param string $key
   *   The array key in the input array which holds the icon information.
   * @param string|null $name
   *   A fallback/default for the icon name, identifying the icon within an svg
   *   file.
   * @param string|null $path
   *   A fallback/default for the path to the icons svg file.
   *   If this is not provided, and the original array does not have a path, the
   *   default icon path from oe_bootstrap_theme is used.
   * @param string|null $size
   *   A fallback/default for the icon size.
   *   Example values are '2xs', 'xs', 'm', 'l', 'xl' etc.
   *
   * @return array|null
   *   One of:
   *     - The (modified) array, where the [$key] key is populated with icon
   *       data, if an icon name was provided either in $argument[$key] or
   *       $argument[$key]['icon'], or in the $name argument.
   *     - The (modified) array, where the [$key] key was removed, if no icon
   *       name was provided.
   */
  #[AsTwigFilter('bcl_expand_icon')]
  public function bclExpandIcon(
    ?array $argument,
    mixed $_ = NamedParametersOnly::SENTINEL,
    string $key = 'icon',
    ?string $name = NULL,
    ?string $path = NULL,
    ?string $size = NULL,
  ): ?array {
    NamedParametersOnly::checkForTwig($_);
    if (isset($argument[$key])) {
      $argument[$key] = $this->bclIcon($argument[$key], name: $name, path: $path, size: $size);
    }
    if (($argument[$key] ?? NULL) === NULL) {
      unset($argument[$key]);
    }
    return $argument;
  }

  /**
   * Populates the 'attributes' key with an Attribute object.
   *
   * @param array|null $argument
   *   An array which may have a [$key] key.
   *   If $argument[$key] is present, it should hold an arguments array, or an
   *   Argument object, or NULL.
   * @param array|null $defaults
   *   Default argument values to merge, or NULL to ignore.
   *   If this is an empty array, an empty Attribute object will always be
   *   created.
   *   If this is NULL, and $argument[$key] is NULL, nothing will be set.
   * @param mixed $_
   *   Internal parameter used for named parameters validation.
   *   Must not be explicitly provided.
   * @param string $key
   *   The array key under which the attributes are found.
   *
   * @return array|null
   *   One of:
   *     - The modified array, with [$key] populated.
   *     - NULL, if the original argument was NULL.
   *     - The unmodified array, if both $argument[$key] and $defaults are NULL
   *       or not provided.
   */
  #[AsTwigFilter('bcl_expand_attributes')]
  public function bclExpandAttributes(
    ?array $argument,
    ?array $defaults = NULL,
    mixed $_ = NamedParametersOnly::SENTINEL,
    string $key = 'attributes',
  ): ?array {
    NamedParametersOnly::checkForTwig($_);
    // Don't add attributes on an empty structure.
    if ($argument === NULL) {
      return NULL;
    }
    if (!isset($argument[$key]) && $defaults === NULL) {
      return $argument;
    }
    if (($argument[$key] ?? []) === []) {
      $argument[$key] = new Attribute($defaults ?? []);
      return $argument;
    }
    if (is_array($argument[$key] ?? [])) {
      $argument[$key] = new Attribute($argument[$key] ?? []);
    }
    if (!$argument[$key] instanceof Attribute) {
      throw new RuntimeError(sprintf(
        "Expected an array, an Attribute object, or NULL at '$key'. Found %s.",
        get_debug_type($argument[$key]),
      ));
    }
    foreach ($defaults ?? [] as $name => $value) {
      if ($value === NULL) {
        continue;
      }
      if ($name === 'class') {
        $argument[$key]->addClass($value);
      }
      elseif (!$argument[$key]->hasAttribute($name)) {
        $argument[$key]->setAttribute($name, $value);
      }
    }
    return $argument;
  }

  /**
   * Removes NULL values from an array.
   *
   * @param array $argument
   *   An array which may contain NULL values.
   * @param bool $preserve_list
   *   TRUE to re-normalize list keys, FALSE to preserve all keys.
   *
   * @return array
   *   The array without NULL values.
   */
  #[AsTwigFilter('without_nulls')]
  public function withoutNulls(array $argument, bool $preserve_list = TRUE): array {
    $result = array_filter($argument, fn ($value) => $value !== NULL);
    if ($preserve_list && array_is_list($argument)) {
      $result = array_values($result);
    }
    return $result;
  }

  /**
   * Creates an Attribute object from input.
   *
   * For now this is used internally and not exposed as a twig filter.
   *
   * @param mixed $argument
   *   The filter argument.
   *
   * @return \Drupal\Core\Template\Attribute
   *   The new attributes object.
   */
  protected function toAttribute(mixed $argument): Attribute {
    if ($argument instanceof Attribute) {
      return $argument;
    }
    if ($argument === NULL) {
      return new Attribute();
    }
    if (is_array($argument)) {
      return new Attribute($argument);
    }
    throw new \InvalidArgumentException(sprintf('Expected an array, an Argument object, or NULL. Found %s.', get_debug_type($argument)));
  }

  /**
   * Applies an arrow function to the value.
   *
   * This is similar to `|invoke()`, but with swapped parameters.
   *
   * @param mixed $argument
   *   The value the filter applies to.
   * @param callable $callback
   *   The arrow function.
   *
   * @return mixed
   *   The result of the callback.
   *
   * @see \Twig\Extension\CoreExtension::invoke()
   */
  #[AsTwigFilter('arrow')]
  public function arrow(mixed $argument, callable $callback): mixed {
    return $callback($argument);
  }

  /**
   * Does an array lookup using the filter argument as key.
   *
   * @param string|int $argument
   *   The filter argument.
   * @param array $branches
   *   The match branches.
   *   By default, the array key 'default' holds the fallback/default value.
   * @param mixed|null $default
   *   A default value, if $branches[$argument] does not exist.
   * @param string|int $default_key
   *   An array key that holds the default value.
   *
   * @return mixed
   *   Either $branches[$argument], or $branches[$default_key], or $default.
   */
  #[AsTwigFilter('match')]
  public function match(string|int $argument, array $branches, mixed $default = NULL, string|int $default_key = 'default'): mixed {
    if (array_key_exists($argument, $branches)) {
      return $branches[$argument];
    }
    if ($default === NULL && array_key_exists($default_key, $branches)) {
      return $branches[$default_key];
    }
    return $default;
  }

  /**
   * Converts an array to an ImageValueObject instance.
   *
   * @param mixed $argument
   *   The argument, which should be an array or ImageValueObjectInterface.
   *
   * @return \Drupal\oe_bootstrap_theme\ValueObject\ImageValueObject
   *   The ImageValueObject instance.
   *   If the value was already an ImageValueObjectInterface object, it will be
   *   copied as an ImageValueObject.
   */
  public function toImageValueObject(mixed $argument): ImageValueObject {
    if (is_array($argument)) {
      if ($argument === []) {
        throw new RuntimeError("The image array is empty..");
      }
      elseif (!isset($argument['src'])) {
        throw new RuntimeError("Missing array key 'src'.");
      }
      return ImageValueObject::fromArray($argument);
    }
    // Convert other implementations of the interface.
    if ($argument instanceof ImageValueObjectInterface) {
      return ImageValueObject::fromArray($argument->getArray());
    }
    throw new RuntimeError(sprintf(
      'Expected ImageValueObjectInterface|array, found %s',
      get_debug_type($argument),
    ));
  }

  /**
   * Converts an array to a FileValueObject instance.
   *
   * @param mixed $argument
   *   The argument, which should be an array or FileValueObjectInterface.
   *
   * @return \Drupal\oe_bootstrap_theme\ValueObject\FileValueObject
   *   The FileValueObject instance.
   *   If the value was already an FileValueObjectInterface object, it will be
   *   copied as an FileValueObject.
   */
  public function toFileValueObject(mixed $argument): FileValueObject {
    if (is_array($argument)) {
      if ($argument === []) {
        throw new RuntimeError("The file array is empty..");
      }
      foreach (['name', 'url', 'mime', 'size'] as $key) {
        if (!isset($argument[$key])) {
          throw new RuntimeError("Missing array key '$key'.");
        }
      }
      return FileValueObject::fromArray($argument);
    }
    // Convert other implementations of the interface.
    if ($argument instanceof FileValueObject) {
      return clone $argument;
    }
    throw new RuntimeError(sprintf(
      'Expected FileValueObject|array, found %s',
      get_debug_type($argument),
    ));
  }

  /**
   * Gets a render array from an ImageValueObject.
   *
   * @param mixed $argument
   *   An ImageValueObjectInterface instance.
   * @param array|\Drupal\Core\Template\Attribute $attributes
   *   Attributes to merge into the render array.
   *
   * @return array
   *   A render array with attributes merged in.
   */
  #[AsTwigFilter('bcl_image_render')]
  public function bclImageRender(mixed $argument, array|Attribute $attributes): array {
    if (!$argument instanceof ImageValueObjectInterface) {
      throw new RuntimeError(sprintf(
        "Expected a render array or ImageValueObjectInterface instance. Found %s",
        get_debug_type($argument),
      ));
    }
    // Convert other implementations.
    $object = ImageValueObject::fromArray($argument->getArray());
    $render = $object->toRenderArray();
    $render['#attributes'] = $attributes;
    return $render;
  }

}
