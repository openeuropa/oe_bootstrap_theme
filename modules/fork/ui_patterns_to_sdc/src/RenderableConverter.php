<?php

declare(strict_types=1);

namespace Drupal\ui_patterns_to_sdc;

use Drupal\Core\Theme\ComponentPluginManager;

/**
 * Convert render arrays.
 */
class RenderableConverter {

  /**
   * Theme, module or profile providing the renderable.
   */
  public string $extension = '';

  public const array COMMON_RENDER_PROPERTIES = [
    'type',
    'id',
    'settings',
    'fields',
    'printed',
    'input',
    'pre_render',
    'cache',
    'context',
    'attached',
    'variant',
    'attributes',
  ];

  public function __construct(
    protected ComponentPluginManager $componentPluginManager,
  ) {}

  /**
   * Set extension (theme, module, profile).
   */
  public function setExtension(string $extension): RenderableConverter {
    $this->extension = $extension;
    return $this;
  }

  /**
   * Convert renderables.
   */
  public function convert(array $renderable): array {
    if (\array_key_exists('type', $renderable) && \array_key_exists('id', $renderable)) {
      $renderable = $this->convertRenderElement($renderable, '');
    }
    if (\array_key_exists('#type', $renderable) && \array_key_exists('#id', $renderable)) {
      $renderable = $this->convertRenderElement($renderable, '#');
    }
    foreach ($renderable as $key => $value) {
      if (!\is_array($value)) {
        continue;
      }
      $renderable[$key] = $this->convert($value);
    }
    return $renderable;
  }

  /**
   * Convert render element (render arrays using #type).
   */
  public function convertRenderElement(array $renderable, string $prefix = '#'): array {
    if ($renderable[$prefix . 'type'] === 'pattern') {
      return $this->convertPattern($renderable, $prefix);
    }
    if ($renderable[$prefix . 'type'] === 'pattern_preview') {
      return $this->convertPatternPreview($renderable, $prefix);
    }
    return $renderable;
  }

  /**
   * Convert legacy render element to SDC render element.
   */
  public function convertPattern(array $element, string $prefix = '#'): array {
    if (!\array_key_exists($prefix . 'id', $element) || !\is_string($element[$prefix . 'id'])) {
      return $element;
    }
    $element[$prefix . 'type'] = 'component';
    $element = $this->resolveCompactFormat($element, $prefix);
    $element[$prefix . 'id'] = $this->getNamespacedId($element[$prefix . 'id']);
    $element[$prefix . 'component'] = $element[$prefix . 'id'];
    unset($element[$prefix . 'id']);
    if (\array_key_exists($prefix . 'fields', $element) && \is_array($element[$prefix . 'fields'])) {
      $slots = $element[$prefix . 'fields'];
      $element[$prefix . 'slots'] = $this->convertSlots($slots, $prefix);
      unset($element[$prefix . 'fields']);
    }
    if (\array_key_exists($prefix . 'settings', $element) && \is_array($element[$prefix . 'settings'])) {
      $element[$prefix . 'props'] = $element[$prefix . 'settings'];
      unset($element[$prefix . 'settings']);
    }
    if (\array_key_exists($prefix . 'variant', $element) && \is_string($element[$prefix . 'variant'])) {
      $element[$prefix . 'props']['variant'] = $element[$prefix . 'variant'];
      unset($element[$prefix . 'variant']);
    }
    return $element;
  }

  /**
   * Convert slots (because SDC is strict).
   */
  private function convertSlots(array $slots, string $prefix = '#'): array {
    foreach ($slots as $slot_id => $slot) {
      // Single scalars are managed by the SDC render element.
      if (!\is_array($slot)) {
        continue;
      }
      // However, list of scalars must be converted to list of render arrays.
      $slots[$slot_id] = $this->convertListSlot($slot, $prefix);
    }
    return $slots;
  }

  /**
   * List of scalars must be converted to list of render arrays.
   */
  public function convertListSlot(array $slot, string $prefix = '#'): array {
    if (!\array_is_list($slot)) {
      return $slot;
    }
    foreach ($slot as $index => $item) {
      if (\is_scalar($item)) {
        $item = [
          $prefix . 'plain_text' => $item,
        ];
      }
      $slot[$index] = $item;
    }
    return $slot;
  }

  /**
   * Convert pattern_preview elements.
   */
  protected function convertPatternPreview(array $element, string $prefix = '#'): array {
    if (!\array_key_exists($prefix . 'id', $element) || !\is_string($element[$prefix . 'id'])) {
      return $element;
    }
    $element[$prefix . 'type'] = 'component';
    $element = $this->resolveCompactFormat($element, $prefix);
    $element[$prefix . 'id'] = $this->getNamespacedId($element[$prefix . 'id']);
    $element[$prefix . 'component'] = $element[$prefix . 'id'];
    $element[$prefix . 'story'] = 'preview';
    unset($element[$prefix . 'id']);
    if (\array_key_exists($prefix . 'variant', $element) && \is_string($element[$prefix . 'variant'])) {
      $element[$prefix . 'props']['variant'] = $element[$prefix . 'variant'];
      unset($element[$prefix . 'variant']);
    }
    return $element;
  }

  /**
   * Resolve UI Patterns 1.x compact format.
   */
  protected function resolveCompactFormat(array $element, string $prefix = '#'): array {
    $component_id = $this->getNamespacedId($element[$prefix . 'id']);
    $definitions = $this->componentPluginManager->getDefinitions();
    if (!isset($definitions[$component_id])) {
      return $element;
    }
    $definition = $definitions[$component_id];
    $slots = \array_keys($definition['slots'] ?? []);
    $props = \array_keys($definition['props']['properties'] ?? []);
    foreach ($element as $property => $value) {
      if (\in_array($property, self::COMMON_RENDER_PROPERTIES, TRUE)) {
        continue;
      }
      $property = \str_replace('#', '', $property);
      if (\in_array($property, $slots, TRUE)) {
        $element[$prefix . 'fields'][$property] = $value;
        unset($element[$prefix . $property]);
        continue;
      }
      if (\in_array($property, $props, TRUE)) {
        $element[$prefix . 'settings'][$property] = $value;
        unset($element[$prefix . $property]);
      }
    }
    return $element;
  }

  /**
   * Get namespaced (SDC style) component ID from UI Patterns 1.x ID.
   */
  public function getNamespacedId(string $component_id): string {
    $parts = \explode(':', $component_id);
    if (\count(\array_filter($parts)) === 2) {
      // Already namespaced.
      return $component_id;
    }
    if (\count(\array_filter($parts)) > 2) {
      // Unexpected situation.
      return $component_id;
    }
    $components = $this->componentPluginManager->getAllComponents();
    // Search first in the 'current' extension (could be active theme, or any
    // other context set by the service calling the converter).
    if ($this->extension) {
      foreach ($components as $component) {
        if ($component->getPluginId() === $this->extension . ':' . $component_id) {
          return $component->getPluginId();
        }
      }
    }
    // If not found in the 'current' extension, return the first found result.
    foreach ($components as $component) {
      $definition = $component->getPluginDefinition();
      $machine_name = \is_array($definition) ? $definition['machineName'] : ($definition->machineName ?? NULL);
      if ($machine_name === $component_id) {
        return $component->getPluginId();
      }
    }
    return $component_id;
  }

}
