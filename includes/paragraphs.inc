<?php

/**
 * @file
 * Theme and preprocess functions for paragraphs.
 */

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Link;
use Drupal\Core\Render\Element;
use Drupal\media\Entity\Media;
use Drupal\media\MediaInterface;
use Drupal\media\MediaSourceInterface;
use Drupal\media\Plugin\media\Source\Image;
use Drupal\media\Plugin\media\Source\OEmbed;
use Drupal\media_avportal\Plugin\media\Source\MediaAvPortalPhotoSource;
use Drupal\media_avportal\Plugin\media\Source\MediaAvPortalSourceInterface;
use Drupal\media_avportal\Plugin\media\Source\MediaAvPortalVideoSource;
use Drupal\oe_bootstrap_theme\ValueObject\ImageValueObject;
use Drupal\oe_media_iframe\Plugin\media\Source\Iframe;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Implements hook_theme_suggestions_HOOK_alter().
 */
function oe_bootstrap_theme_theme_suggestions_paragraph_alter(array &$suggestions, array $variables): void {
  /** @var \Drupal\paragraphs\ParagraphInterface $paragraph */
  $paragraph = $variables['elements']['#paragraph'];

  if ($paragraph->bundle() === 'oe_content_row') {
    $variant = $paragraph->get('oe_paragraphs_variant')->first()->value;
    $suggestions[] = 'paragraph__' . $paragraph->bundle() . '__variant_' . $variant;
  }
}

/**
 * Implements hook_preprocess_paragraph__oe_accordion().
 */
function oe_bootstrap_theme_preprocess_paragraph__oe_accordion(array &$variables): void {
  // Massage data to be compliant with OE Bootstrap Theme accordion component
  // data structure.
  $builder = \Drupal::entityTypeManager()->getViewBuilder('paragraph');
  $variables['items'] = [];

  /** @var \Drupal\entity_reference_revisions\Plugin\Field\FieldType\EntityReferenceRevisionsItem $field_item */
  foreach ($variables['paragraph']->get('field_oe_paragraphs') as $field_item) {
    $paragraph = \Drupal::service('entity.repository')->getTranslationFromContext($field_item->entity);
    $variables['items'][] = [
      'title' => $builder->viewField($paragraph->get('field_oe_text')),
      'content' => $builder->viewField($paragraph->get('field_oe_text_long')),
    ];
  }
}

/**
 * Implements hook_preprocess_paragraph().
 */
function oe_bootstrap_theme_preprocess_paragraph__oe_links_block(array &$variables): void {
  // Massage data to be compliant with ECL links block component data structure.
  foreach (Element::children($variables['content']['field_oe_links']) as $index) {
    $variables['links'][] = [
      'label' => $variables['content']['field_oe_links'][$index]['#title'],
      'url' => $variables['content']['field_oe_links'][$index]['#url'],
    ];
  }
}

/**
 * Implements hook_preprocess_paragraph() for paragraph--oe-text-feature-media.html.twig.
 */
function oe_bootstrap_theme_preprocess_paragraph__oe_text_feature_media(array &$variables): void {
  $variables['image'] = [];

  /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
  $paragraph = $variables['paragraph'];

  /** @var \Drupal\media\Entity\Media $media */
  $media = $paragraph->get('field_oe_media')->entity;

  // Bail out if media entity is not available or there is no media present.
  $no_media = !$media instanceof MediaInterface || $paragraph->get('field_oe_media')->isEmpty();
  if ($no_media) {
    return;
  }

  $cacheability = CacheableMetadata::createFromRenderArray($variables);

  // Retrieve the correct media translation.
  /** @var \Drupal\media\Entity\Media $media */
  $media = \Drupal::service('entity.repository')->getTranslationFromContext($media, $paragraph->language()->getId());

  // Caches are handled by the formatter usually. Since we are not rendering
  // the original render arrays, we need to propagate our caches to the
  // paragraph template.
  $cacheability->addCacheableDependency($media);

  // Run access checks on the media entity.
  $access = $media->access('view', $variables['user'], TRUE);
  $cacheability->addCacheableDependency($access);
  if (!$access->isAllowed()) {
    $cacheability->applyTo($variables);
    return;
  }

  // Get the media source.
  $source = $media->getSource();

  $is_image = $source instanceof MediaAvPortalPhotoSource || $source instanceof Image;
  $is_video = $source instanceof MediaAvPortalVideoSource || $source instanceof OEmbed || $source instanceof Iframe;

  // If it's no image or no video, bail out.
  if (!$is_image && !$is_video) {
    return;
  }

  // If it's an image media, render it and assign it to the image variable.
  set_image($is_image, $media, $variables, $cacheability);
  set_embedded_media($variables, $paragraph, $media, $cacheability, $source);
}

/**
 * Set the image.
 */
function set_image(bool $is_image, Media $media, array &$variables, CacheableMetadata $cacheability): array {
  if (!$is_image) {
    return $variables;
  }

  $thumbnail = $media->get('thumbnail')->first();
  $variables['image'] = ImageValueObject::fromStyledImageItem($thumbnail, 'oe_bootstrap_theme_medium_no_crop');
  // Overwrite the variant because is an image.
  $variables['variant'] = 'image';
  $cacheability->applyTo($variables);

  return $variables;
}

/**
 * Implements hook_preprocess_paragraph() for paragraph--oe-list-item-block.html.twig.
 */
function oe_bootstrap_theme_preprocess_paragraph__oe_list_item_block(array &$variables): void {
  /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
  $paragraph = $variables['paragraph'];
  $variables['title'] = $paragraph->get('field_oe_title')->first()->value;
  $variables['columns'] = get_bcl_layout_mapping($paragraph->get('field_oe_list_item_block_layout')->first()->value);
  $variables['items'] = [];

  foreach ($variables['paragraph']->get('field_oe_paragraphs') as $delta => $item) {
    $image = [];
    if (!$item->entity->get('field_oe_image')->isEmpty()) {
      /** @var \Drupal\file\Plugin\Field\FieldType\FileItem $image_item */
      $image_item = $item->entity->get('field_oe_image')->first();
      $file = $image_item->get('entity')->getValue();
      $image = [
        'path' => file_url_transform_relative(file_create_url($file->getFileUri())),
        'alt' => $image_item->get('alt')->getValue(),
        'position' => 'top',
      ];
      if ($item->entity->get('oe_paragraphs_variant')->first()->value === 'thumbnail_secondary') {
        $image['position'] = 'bottom';
      }
    }

    // Prepare the date fields if date is available.
    $contentItem = '';
    if (!$item->entity->get('field_oe_date')->isEmpty()) {
      $display_options = EntityViewDisplay::load('paragraph.oe_list_item.default')
        ->getComponent('field_oe_date');
      $dateItem = $item->entity->get('field_oe_date')->first();
      $contentItem = [
        '#type' => 'inline_template',
        '#template' => '<div><span class="d-md-inline d-block text-muted mb-2 mb-md-0">{{ date }}</span></div>',
        '#context' => [
          'date' => $dateItem->view($display_options),
        ],
      ];
    }

    // Prepare the metas if available.
    $metas = [];
    if (!$item->entity->get('field_oe_meta')->isEmpty()) {
      foreach ($item->entity->get('field_oe_meta') as $item_meta) {
        $metas[] = [
          'label' => $item_meta->value,
          'background' => 'primary',
        ];
      }
    }

    $variables['items'][] = [
      'title' => [
        'content' => Link::fromTextAndUrl(
          $item->entity->field_oe_title->value,
          $item->entity->get('field_oe_link')->first()->getUrl()
        ),
      ],
      'text' => [
        'content' => $item->entity->field_oe_text_long->value,
        'classes' => 'mb-2',
      ],
      'image' => $image,
      'badges' => $metas,
      'content' => $contentItem,
    ];
  }

  // Prepare the button variables if a link has been specified.
  if ($paragraph->get('field_oe_link')->isEmpty()) {
    return;
  }

  /** @var \Drupal\link\Plugin\Field\FieldType\LinkItem $link_item */
  $link_item = $paragraph->get('field_oe_link')->first();
  $variables['link'] = [
    'path' => $link_item->getUrl()->toString(),
    'label' => $link_item->get('title')->getValue(),
    'icon' => [
      'path' => $variables['bcl_icon_path'],
      'name' => 'chevron-right',
    ],
  ];
}

/**
 * Gets the mapped layout.
 *
 * @param string $layout
 *   The paragraph layout.
 *
 * @return string
 *   The BCL layout.
 */
function get_bcl_layout_mapping(string $layout): string {
  if ($layout === 'three_columns') {
    return 'highlight-3-col';
  }
  if ($layout === 'two_columns') {
    return 'highlight-2-col';
  }

  return 'highlight-1-col';
}

/**
 * Implements hook_preprocess_paragraph() for oe_banner paragraph.
 */
function oe_bootstrap_theme_preprocess_paragraph__oe_banner(array &$variables): void {
  /** @var Drupal\paragraphs\Entity\Paragraph $paragraph */
  $paragraph = $variables['paragraph'];
  $variables['title'] = $paragraph->get('field_oe_title')->value;
  $variables['description'] = $paragraph->get('field_oe_text')->value;
  $variables['full_width'] = (bool) $paragraph->get('field_oe_banner_full_width')->value;
  set_banner_link($paragraph, $variables);

  // The alignment field value contains the information regarding the pattern
  // type and centering.
  $alignment = $paragraph->get('field_oe_banner_type')->value;
  [$banner_type, $banner_alignment] = explode('_', $alignment);
  // The beginning of the string determines the pattern.
  $variables['pattern'] = 'banner_' . $banner_type;
  // The end of the string determines the position.
  $variables['alignment'] = $banner_alignment;
  $variables['variant'] = get_bcl_variant($paragraph);

  if ($variables['variant'] === 'default' || $variables['variant'] === 'primary') {
    return;
  }

  // Bail out if there is no media present.
  if ($paragraph->get('field_oe_media')->isEmpty()) {
    return;
  }
  $cacheability = CacheableMetadata::createFromRenderArray($variables);

  /** @var \Drupal\media\Entity\Media $media */
  $media = $paragraph->get('field_oe_media')->entity;
  if (!$media instanceof MediaInterface) {
    // The media entity is not available anymore, bail out.
    return;
  }

  // Retrieve the correct translation to display.
  $media = \Drupal::service('entity.repository')->getTranslationFromContext($media, $paragraph->language()->getId());

  // Caches are handled by the formatter usually. Since we are not rendering
  // the original render arrays, we need to propagate our caches to the
  // paragraph template.
  $cacheability->addCacheableDependency($media);

  // Run access checks on the media entity.
  $access = $media->access('view', $variables['user'], TRUE);
  $cacheability->addCacheableDependency($access);
  if (!$access->isAllowed()) {
    $cacheability->applyTo($variables);
    return;
  }

  $source = $media->getSource();
  // We only support images and AV Portal photos for now.
  if (!$source instanceof MediaAvPortalSourceInterface && !$source instanceof Image) {
    $cacheability->applyTo($variables);
    return;
  }

  $uri = get_media_uri($source, $media, $cacheability);

  // The uri might be empty if the source is of type Image and the file entity
  // was deleted.
  if (empty($uri)) {
    $cacheability->applyTo($variables);
    return;
  }

  $variables['image'] = ImageValueObject::fromArray([
    'src' => file_create_url($uri),
    'alt' => $source->getMetadata($media, 'thumbnail_alt_value') ?? $media->label(),
    'name' => $media->getName(),
  ]);
  $cacheability->applyTo($variables);
}

/**
 * Sets link variable for banner paragraph.
 *
 * @param \Drupal\paragraphs\Entity\Paragraph $paragraph
 *   The paragraph.
 * @param array $variables
 *   The render array.
 */
function set_banner_link(Paragraph $paragraph, array &$variables): void {
  if ($paragraph->get('field_oe_link')->isEmpty()) {
    return;
  }

  $link = $paragraph->get('field_oe_link')->first();
  $variables['url'] = $link->getUrl()->toString();
  $variables['label'] = $link->get('title')->getValue();
}

/**
 * Gets the uri from a media object.
 *
 * @param \Drupal\media\MediaSourceInterface $source
 *   The media source.
 * @param \Drupal\media\Entity\Media $media
 *   The media object.
 * @param \Drupal\Core\Cache\CacheableMetadata $cacheability
 *   The cacheability object.
 *
 * @return string
 *   The uri string.
 */
function get_media_uri(MediaSourceInterface $source, Media $media, CacheableMetadata $cacheability): string {
  $field_name = $source->getConfiguration()['source_field'];

  if ($source instanceof Image && ($file_entity = $media->get($field_name)->entity)) {
    $cacheability->addCacheableDependency($file_entity);
    return $file_entity->getFileUri();
  }

  if ($source instanceof MediaAvPortalSourceInterface) {
    $resource_ref = $media->get($field_name)->value;
    return 'avportal://' . $resource_ref . '.jpg';
  }

  return '';
}

/**
 * Gets the BCL variant name.
 *
 * @param \Drupal\paragraphs\Entity\Paragraph $paragraph
 *   The paragraph.
 *
 * @return string
 *   The computed pattern variant name.
 */
function get_bcl_variant(Paragraph $paragraph): string {
  if ($paragraph->get('oe_paragraphs_variant')->isEmpty()) {
    return 'default';
  }

  $variant = $paragraph->get('oe_paragraphs_variant')->first()->value;
  if (!$variant) {
    return 'default';
  }

  switch ($paragraph->getType()) {
    case 'oe_banner':
      return str_replace('oe_banner_', '', $variant);

    case 'oe_text_feature_media':
      return str_replace(['_featured', '_simple'], '', $variant);

    default:
      return $variant;
  }

}

/**
 * Gets the BCL variant name.
 */
function set_embedded_media(array &$variables, Paragraph $paragraph, Media $media, CacheableMetadata $cacheability, $source) {
  $variables['variant'] = 'inside_text';
  $variables['alignment'] = get_bcl_variant($paragraph);
  // Default video aspect ratio is set to 16x9.
  $variables['ratio'] = '16x9';

  // Load information about the media and the display.
  $media_type = \Drupal::entityTypeManager()->getStorage('media_type')->load($media->bundle());
  $cacheability->addCacheableDependency($media_type);
  $source_field = $source->getSourceFieldDefinition($media_type);
  $display = EntityViewDisplay::collectRenderDisplay($media, 'oe_theme_main_content');
  $cacheability->addCacheableDependency($display);
  $display_options = $display->getComponent($source_field->getName());

  // If it is an OEmbed resource, render it and pass it as embeddable data
  // only if it is of type video or html.
  if ($source instanceof OEmbed) {
    $oembed_type = $source->getMetadata($media, 'type');
    if (in_array($oembed_type, ['video', 'html'])) {
      $variables['embedded_media'] = $media->{$source_field->getName()}->view($display_options);
      $cacheability->applyTo($variables);
      return;
    }
  }

  // If its an AvPortal video or an iframe video, render it.
  $variables['embedded_media'] = $media->{$source_field->getName()}->view($display_options);
  $cacheability->applyTo($variables);
  set_ratio($media, $variables, $cacheability);
  return $variables;
}

/**
 * Set the video iframe ratio.
 */
function set_ratio(Media $media, array &$variables, CacheableMetadata $cacheability): void {
  // When dealing with iframe videos, also respect its given aspect ratio.
  if ($media->bundle() === 'video_iframe') {
    $ratio = $media->get('oe_media_iframe_ratio')->value;
    $variables['ratio'] = str_replace('_', 'x', $ratio);
    $cacheability->applyTo($variables);
  }
}
