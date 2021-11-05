<?php

/**
 * @file
 * Theme and preprocess functions for paragraphs.
 */

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Render\Element;
use Drupal\media\MediaInterface;
use Drupal\media\Plugin\media\Source\Image;
use Drupal\media_avportal\Plugin\media\Source\MediaAvPortalPhotoSource;
use Drupal\oe_bootstrap_theme\ValueObject\ImageValueObject;

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

  // Link field doesn't exist in oe_paragraphs <= 1.8.0, so ensure it exists.
  if ($paragraph->hasField('field_oe_link') && !$paragraph->get('field_oe_link')->isEmpty()) {
    $link_field = $paragraph->get('field_oe_link')->first();
    $variables['link'] = [
      'path' => $link_field->getUrl()->toString(),
      'label' => $link_field->get('title')->getValue(),
    ];
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

  // Retrieve the correct media translation.
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

  // If it's an image media, render it and assign it to the image variable.
  if ($source instanceof MediaAvPortalPhotoSource || $source instanceof Image) {
    $thumbnail = $media->get('thumbnail')->first();
    $variables['image'] = ImageValueObject::fromStyledImageItem($thumbnail, 'oe_bootstrap_theme_medium_no_crop');
    $cacheability->applyTo($variables);
    // Overwrite the variant because is an image.
    $variables['variant'] = 'image';
    return;
  }

  $cacheability->applyTo($variables);
}

/**
 * Implements hook_preprocess_paragraph() for paragraph--oe-list-item-block.html.twig.
 */
function oe_bootstrap_theme_preprocess_paragraph__oe_list_item_block(array &$variables): void {
  /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
  $paragraph = $variables['paragraph'];
  $variables['title'] = $paragraph->get('field_oe_title')->first()->value;
  // Set up the correct variant name.
  $variant_block = $paragraph->get('field_oe_list_item_block_layout')->first()->value;
  set_variant_block($variant_block);
  $variables['variant'] = 'highlight-' . $variant_block;
  $variables['items'] = [];

  foreach ($variables['paragraph']->get('field_oe_paragraphs') as $delta => $item) {
    $variant = $item->entity->get('oe_paragraphs_variant')->first()->value;
    if ($variant == 'highlight') {
      $text_classes = 'mb-2';
    }
    else {
      $text_classes = 'mb-3';
    }

    $image = [];
    if (!$item->entity->get('field_oe_image')->isEmpty()) {
      /** @var \Drupal\file\Plugin\Field\FieldType\FileItem $image_item */
      $image_item = $item->entity->get('field_oe_image')->first();
      $file = $image_item->get('entity')->getValue();

      $image = [
        'path' => file_url_transform_relative(file_create_url($file->getFileUri())),
        'alt' => $image_item->get('alt')->getValue(),
      ];

      set_image($variant, $image);
    }
    $title_content = $item->entity->field_oe_title->value;
    $url_title = $item->entity->get('field_oe_link')->first()->getUrl()->toString();
    if (!empty($url_title)) {
      $title_content = [
        '#markup' => '<a href="' . $url_title . '">' . $title_content . '</a>',
      ];
    }

    // Prepare the date fields if date is available.
    $contentItem = "";
    if (!$item->entity->get('field_oe_date')->isEmpty()) {
      $dateItem = $item->entity->get('field_oe_date')->first();
      $contentItem = [
        '#markup' => '<div><span class="d-md-inline d-block text-muted mb-2 mb-md-0">' . $dateItem->date->format("j F Y") . '</span></div>',
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
        'content' => $title_content,
      ],
      'text' => [
        'content' => $item->entity->field_oe_text_long->value,
        'classes' => $text_classes,
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
    'icon' => [],
  ];
}

/**
 * Sets variant block.
 */
function set_variant_block(&$variant_block): void {
  switch ($variant_block) {
    case 'one_column':
      $variant_block = '1-col';
      break;

    case 'two_columns':
      $variant_block = '2-col';
      break;

    case 'three_columns':
      $variant_block = '3-col';
      break;
  }
}

/**
 * Sets image position and rounded.
 */
function set_image($variant, array &$image): void {
  if ($variant == 'thumbnail_secondary') {
    $image['position'] = 'bottom';
  }
  elseif ($variant == 'thumbnail_secondary') {
    $image['position'] = 'top';
  }
  else {
    $image['rounded'] = 1;
  }
}
