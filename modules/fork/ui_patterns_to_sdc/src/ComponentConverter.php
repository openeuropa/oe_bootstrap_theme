<?php

declare(strict_types=1);

namespace Drupal\ui_patterns_to_sdc;

use Drupal\Core\Render\Component\Exception\InvalidComponentDataException;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Theme\Component\ComponentValidator;

/**
 * Component converter.
 */
class ComponentConverter {

  /**
   * Theme, module or profile providing the component.
   */
  protected string $extension;

  public function __construct(
    private readonly RendererInterface $renderer,
  ) {}

  /**
   * Set extension (theme, module, profile).
   */
  public function setExtension(string $extension): ComponentConverter {
    $this->extension = $extension;
    return $this;
  }

  /**
   * Convert component definition.
   */
  public function convert(array $source): array {
    $target = [];
    $target = $this->addProperty('label', $source, 'name', $target);
    $target = $this->addProperty('description', $source, 'description', $target);
    $target = $this->addProperty('category', $source, 'group', $target);
    $target = $this->addProperty('use', $source, '_template', $target);
    $target = $this->processLibraryProperties($source, $target);
    $target = $this->processLayoutProperties($source, $target);
    if (\array_key_exists('variants', $source)) {
      $target['variants'] = $this->getVariants($source);
    }
    if (\array_key_exists('fields', $source)) {
      $target['slots'] = $this->getSlotsFromFields($source['fields']);
    }
    if (\array_key_exists('settings', $source)) {
      $target['props'] = [
        'type' => 'object',
        'properties' => $this->getPropsFromSettings($source['settings']),
      ];
      $required_props = $this->getRequiredPropsFromSettings($source['settings']);
      if (!empty($required_props)) {
        $target['props']['required'] = $required_props;
      }
    }
    if (\array_key_exists('libraries', $source)) {
      $target['libraryOverrides'] = $this->getLibrary($source);
    }
    return $target;
  }

  /**
   * Process ui_patterns_library properties.
   */
  private function processLibraryProperties(array $source, array $target): array {
    $target = $this->addProperty('links', $source, 'links', $target);
    return $this->addProperty('tags', $source, 'tags', $target);
  }

  /**
   * Process layout discovery properties.
   */
  private function processLayoutProperties(array $source, array $target): array {
    // Describe the icon used when the site builder is choosing a layout in the
    // layout builder interface, these icons are built as SVG automatically.
    $target = $this->addProperty('icon_map', $source, 'icon_map', $target);
    // Path (relative to the module or theme) to resources like icon or
    // template.
    $target = $this->addProperty('path', $source, 'path', $target);
    // The path to the preview image (relative to the 'path' given).
    return $this->addProperty('icon', $source, 'icon', $target);
  }

  /**
   * Get component variants.
   */
  private function getVariants(array $source): array {
    $variants = [];
    foreach ($source['variants'] as $variant_id => $variant) {
      if (\is_array($variant)) {
        $new = [];
        $new = $this->addProperty('label', $variant, 'title', $new);
        $new = $this->addProperty('description', $variant, 'description', $new);
        $variants[$variant_id] = $new;
      }
      if (!\is_string($variant)) {
        continue;
      }

      $variants[$variant_id] = [
        'title' => $variant,
      ];
    }

    return $variants;
  }

  /**
   * Get SDC slots from UI Patterns 1.x fields.
   */
  private function getSlotsFromFields(array $fields): array {
    $slots = [];
    foreach ($fields as $field_id => $field) {
      $slot = [];
      $slot = $this->addProperty('label', $field, 'title', $slot);
      $slot = $this->addProperty('description', $field, 'description', $slot);
      $slots[$field_id] = $slot;
    }
    return $slots;
  }

  /**
   * Get SDC props from UI Patterns 1.x settings.
   */
  private function getPropsFromSettings(array $settings): array {
    $converter = new PropConverter();
    $props = [];
    foreach ($settings as $setting_id => $setting) {
      $prop = [];
      $prop = $this->addProperty('label', $setting, 'title', $prop);
      $prop = $this->addProperty('description', $setting, 'description', $prop);
      $prop = \array_merge($prop, $converter->convert($setting));
      if (\array_key_exists('default_value', $setting)) {
        $prop['default'] = $setting['default_value'];
      }
      $props[$setting_id] = $prop;
    }
    return $props;
  }

  /**
   * Get required props from UI Patterns 1.x settings.
   */
  private function getRequiredPropsFromSettings(array $settings): array {
    $props = [];
    foreach ($settings as $setting_id => $setting) {
      if (\array_key_exists('required', $setting) && $setting['required']) {
        $props[] = $setting_id;
      }
    }
    return $props;
  }

  /**
   * A small helper to convert a property.
   */
  protected function addProperty(string $source_key, array $source, string $target_key, array $target): array {
    if (!\array_key_exists($source_key, $source)) {
      return $target;
    }
    if ($source[$source_key] === NULL) {
      return $target;
    }
    $value = $source[$source_key];
    if (!\array_key_exists($target_key, $target) || empty($target[$target_key])) {
      // If not already set, everything is fine, we can set.
      $target[$target_key] = $value;
    }
    elseif (\is_array($value)) {
      // If already set, we merge if it is an array.
      $target[$target_key] = \array_merge_recursive($target[$target_key], $value);
    }

    return $target;
  }

  /**
   * Merge libraries into one.
   */
  private function getLibrary(array $source): array {
    // Libraries are simpler with SDC.
    // They are a single consolidated definition instead of a list of dicts or
    // strings.
    $consolidated_library = [];
    $dependencies = [];
    foreach ($source['libraries'] as $library) {
      if (\is_string($library)) {
        $dependencies[] = $library;
        continue;
      }
      foreach ($library as $definition) {
        $consolidated_library = \array_merge($consolidated_library, $definition);
      }
    }
    if (empty($dependencies)) {
      return $consolidated_library;
    }
    $consolidated_library = \array_merge(
      [
        'dependencies' => $dependencies,
      ],
      $consolidated_library,
    );
    // For each component, SDC adds a library with the name
    // "sdc/{extension}--{machine_name_with_dashes}.
    // For example, for a component with the name "my-banner" on theme
    // "my_theme" the asset library is "sdc/my_theme--my-banner".
    // On UI Patterns 1.x, it was "ui_patterns/{component_id}.{library_id}".
    foreach ($consolidated_library['dependencies'] as $index => $dependency) {
      if (\str_starts_with($dependency, 'ui_patterns/') && \str_contains($dependency, '.')) {
        $component_id = (\preg_split('/(\/|\.)/', $dependency) ?: [$dependency])[1];
        $component_id = \str_replace('_', '-', $component_id);
        $consolidated_library['dependencies'][$index] = 'sdc/' . $this->extension . '--' . $component_id;
      }
    }
    return $consolidated_library;
  }

  /**
   * Validate the converted definition.
   */
  public function validate(array $definition): array {
    $errors = [];
    $sdc_validator = new ComponentValidator();
    $is_valid = $sdc_validator->validateDefinition($definition, FALSE);
    if (!$is_valid) {
      $errors[] = \sprintf('Component "%s" definition is not valid.', $definition['id']);
    }
    if (!isset($definition['stories'])) {
      return $errors;
    }
    foreach (\array_keys($definition['stories']) as $story_id) {
      $renderable = [
        '#type' => 'component_story',
        '#component' => $definition['id'],
        '#story' => $story_id,
      ];
      try {
        $this->renderer->renderInIsolation($renderable);
      }
      catch (InvalidComponentDataException $e) {
        $errors[] = $e->getMessage();
      }
    }
    return $errors;
  }

}
