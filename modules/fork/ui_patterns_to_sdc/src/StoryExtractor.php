<?php

declare(strict_types=1);

namespace Drupal\ui_patterns_to_sdc;

/**
 * Extract "preview" story from fields & settings preview properties.
 */
class StoryExtractor {

  /**
   * Theme, module or profile providing the renderable.
   */
  protected string $extension = '';

  public function __construct(
    protected RenderableConverter $renderableConverter,
  ) {}

  /**
   * Set extension (theme, module, profile).
   */
  public function setExtension(string $extension): StoryExtractor {
    $this->extension = $extension;
    return $this;
  }

  /**
   * Convert component definition.
   */
  public function extract(array $source): array {
    if (!\array_key_exists('fields', $source) && !\array_key_exists('settings', $source)) {
      return [];
    }
    $story = [
      'name' => 'Preview',
    ];
    if (\array_key_exists('fields', $source)) {
      $story = $this->getSlotsFromFields($source['fields'], $story);
    }
    if (\array_key_exists('settings', $source)) {
      $story = $this->getPropsFromSettings($source['settings'], $story);
    }
    return $story;
  }

  /**
   * Extract slots from fields previews.
   */
  private function getSlotsFromFields(array $fields, array $story): array {
    $this->renderableConverter->setExtension($this->extension);
    foreach ($fields as $field_id => $field) {
      if (!\array_key_exists('preview', $field)) {
        continue;
      }
      $renderable = $field['preview'];
      if (empty($renderable)) {
        continue;
      }
      if (\is_array($renderable)) {
        $renderable = $this->renderableConverter->convert($renderable);
        $renderable = $this->renderableConverter->convertListSlot($renderable, '');
      }
      $story['slots'][$field_id] = $renderable;
    }

    return $story;
  }

  /**
   * Extract props from settings previews.
   */
  private function getPropsFromSettings(array $settings, array $story): array {
    foreach ($settings as $setting_id => $setting) {
      $value = $this->getPropFromSetting($setting, 'preview');
      if ($value === NULL) {
        // 'default_value' is used as a fallback for 'preview' in
        // ui_patterns_settings 2.x
        $value = $this->getPropFromSetting($setting, 'default_value');
      }
      if ($value === NULL) {
        continue;
      }
      $story['props'][$setting_id] = $value;
    }
    return $story;
  }

  /**
   * Process a specific setting.
   */
  private function getPropFromSetting(array $setting, string $key = 'preview'): mixed {
    if (!\array_key_exists($key, $setting)) {
      return NULL;
    }
    // To avoid some JSON schema validation issue, when an empty PHP array can
    // be serialize as a JSON array of a JSON object.
    if (\is_array($setting[$key]) && empty($setting[$key])) {
      return NULL;
    }
    if ($setting['type'] === 'select') {
      return $this->getPropFromSelectSetting($setting, $key);
    }
    if ($setting['type'] === 'checkboxes') {
      return $this->getPropFromCheckboxesSetting($setting, $key);
    }
    if ($setting['type'] === 'attributes' && \is_string($setting[$key])) {
      return $this->convertAttributes($setting, $key);
    }
    return $setting[$key];
  }

  /**
   * Process select setting.
   */
  private function getPropFromSelectSetting(array $setting, string $key = 'preview'): mixed {
    if (!\array_key_exists('options', $setting)) {
      return NULL;
    }
    if (!\in_array($setting[$key], \array_keys($setting['options']), TRUE)) {
      // Don't add value not compliant with the definition.
      return NULL;
    }
    return $this->switchEnumValueType($setting[$key], \array_keys($setting['options']));
  }

  /**
   * Process checkboxes setting.
   */
  private function getPropFromCheckboxesSetting(array $setting, string $key = 'preview'): mixed {
    if (!\array_key_exists('options', $setting)) {
      return NULL;
    }
    if (!\is_array($setting[$key])) {
      return NULL;
    }
    foreach ($setting[$key] as $index => $value) {
      $setting[$key][$index] = $this->switchEnumValueType($value, \array_keys($setting['options']));
    }
    return $setting[$key];
  }

  /**
   * Switch value type if different from the enum.
   */
  private function switchEnumValueType(mixed $value, array $enum): mixed {
    // Has value the same type as the one from enum ?
    if (\in_array($value, $enum, TRUE) && !\in_array($value, $enum, TRUE)) {
      // Value is a PHP array key, so only string or integer.
      $value = \is_int($value) ? (string) $value : (int) $value;
    }
    return $value;
  }

  /**
   * In UI Patterns 2, attributes values are not stored as strings anymore.
   */
  private function convertAttributes(array $setting, string $key = 'preview'): array {
    $preview = [];
    $dom = new \DOMDocument();
    $dom->loadHTML('<span ' . $setting[$key] . '>');
    $attributes = $dom->getElementsByTagName('span')->item(0)->attributes;
    foreach ($attributes as $attribute) {
      $preview[$attribute->name] = $attribute->value;
    }
    return $preview;
  }

}
