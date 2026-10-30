<?php

declare(strict_types=1);

namespace Drupal\oe_bootstrap_theme_sdc_port;

use Drupal\Core\Render\RendererInterface;
use Drupal\ui_patterns_to_sdc\ComponentConverter;

/**
 * Service override for the component converter.
 */
class ComponentConverterDecorator extends ComponentConverter {

  public function __construct(
    RendererInterface $renderer,
    private readonly TwigStoryWriter $twigStoryWriter,
  ) {
    parent::__construct($renderer);
  }

  /**
   * {@inheritdoc}
   */
  public function convert(array $source): array {
    $source = $this->processSource($source);
    $target = parent::convert($source);
    $target = $this->processTarget($target, $source, $source['id']);
    $this->twigStoryWriter->createAndSaveStory($target, $source);
    return $target;
  }

  /**
   * Prepares a pattern definition to use as component source.
   *
   * @param array $source
   *   The pattern definition.
   *
   * @return array
   *   The altered pattern definition.
   */
  private function processSource(array $source) {
    foreach ($source['fields'] ?? [] as $field_name => $field_definition) {
      $use_slot = $this->fieldShouldBeSlot($field_definition);
      if (!$use_slot) {
        $source['settings'][$field_name] = $field_definition;
        unset($source['fields'][$field_name]);
      }
    }
    return $source;
  }

  /**
   * Determines if a field should be a slot.
   *
   * @param array $field_definition
   *   The pattern field definition.
   *
   * @return bool
   *   TRUE for slot, FALSE for prop.
   */
  protected function fieldShouldBeSlot(array $field_definition): bool {
    // For now, everything is a slot, nothing is a prop.
    // @todo Rethink for 'render', 'text', 'markup' types.
    return FALSE;
  }

  /**
   * Alters a component definition that was created based on a pattern.
   *
   * @param array $target
   *   The component definition.
   * @param array $source
   *   The pattern definition.
   * @param string $id
   *   The pattern id, which is also part of the component id.
   *
   * @return array
   *   The altered component definition.
   */
  private function processTarget(array $target, array $source, string $id) {
    if (empty($target['slots'])) {
      unset($target['slots']);
    }
    if (isset($target['props']['properties'])) {
      $target['props']['properties'] = $this->processProps(
        $target['props']['properties'],
        $source['settings'] ?? [],
        $id,
      );
    }
    return $target;
  }

  /**
   * Alters component props.
   *
   * @param array<string, array> $props
   *   The prop definitions to alter.
   * @param array $source_settings
   *   The pattern setting definitions from which the props were created.
   * @param string $id
   *   The pattern id.
   *
   * @return array<string, array>
   *   The altered prop definitions.
   */
  protected function processProps(array $props, array $source_settings, string $id): array {
    foreach ($props as $name => $prop) {
      $source_setting = $source_settings[$name]
        ?? throw new \Exception("Source setting not found for '$name' in '$id'.");
      assert(is_array($source_setting), "setting for '$name' in '$id'.");
      $props[$name] = $this->processProp(
        $prop,
        $source_setting,
        $name,
        $id,
      );
    }
    // Attributes are always the last prop.
    $props = [
      ...array_diff_key($props, ['attributes' => TRUE]),
      ...$props,
    ];
    // All components have an attributes prop.
    $props['attributes'] ??= [
      'title' => 'Attributes',
      'type' => ['object', 'array', 'string', 'null'],
    ];
    return $props;
  }

  /**
   * Alters a prop definition.
   *
   * @param array $prop
   *   The prop definition.
   * @param array $source_setting
   *   The pattern setting definition.
   * @param string $name
   *   The prop name.
   * @param string $id
   *   The pattern id.
   *
   * @return array
   *   The altered prop definition.
   */
  protected function processProp(array $prop, array $source_setting, string $name, string $id): array {
    $prop_type_parts = [];
    foreach ($prop as $pk => $pv) {
      if ($pk === 'title' || $pk === 'description' || $pk === 'meta:enum' || $pk === 'default') {
        continue;
      }
      if (is_string($pv)) {
        $prop_type_parts[] = $pk . ':' . $pv;
      }
      else {
        $prop_type_parts[] = $pk;
      }
    }
    $prop['type'] = $this->findPropType($prop, $source_setting, $name, $id);
    // Allow string representation of numbers.
    if (array_intersect(['integer', 'number'], (array) $prop['type'])) {
      $prop['type'] = array_unique([...(array) $prop['type'], 'string']);
    }
    unset($prop['$ref']);
    // For now, all props are optional, so we allow NULL.
    if (!is_string($prop['type']) ||
      (!str_contains($prop['type'], '\\') && !class_exists($prop['type']) && !interface_exists($prop['type']))
    ) {
      $prop['type'] = array_unique([...(array) $prop['type'], 'null']);
    }
    // Change verb form in prop description.
    if (preg_match('#^(Add|Make|Enable|Display|Link|Use)( .*)#', $prop['description'] ?? '?', $matches)) {
      $prop['description'] = $matches[1] . 's' . $matches[2];
    }
    // Remove dot in the prop title.
    if (preg_match('#^(.*\w)\.#', $prop['title'] ?? '?', $matches)) {
      $prop['title'] = $matches[1];
    }
    // Capitalize enum labels.
    foreach ($prop['meta:enum'] ?? [] as $enum_value => $enum_label) {
      $prop['meta:enum'][$enum_value] = ucfirst($enum_label);
    }
    // Add null enum option.
    if (isset($prop['enum']) && !in_array(NULL, $prop['enum'], TRUE)) {
      $prop['enum'][] = NULL;
      if (isset($prop['meta:enum'])) {
        $prop['meta:enum'][''] ??= 'Default';
      }
    }
    return $prop;
  }

  /**
   * Determines a type for a prop.
   *
   * @param array $prop
   *   The prop as created by the parent.
   * @param array $setting
   *   The pattern setting definition.
   * @param string $name
   *   The prop name.
   * @param string $id
   *   The pattern id.
   *
   * @return string|list<string>
   *   The prop type.
   *
   * @throws \Exception
   *   A prop type was not found.
   */
  protected function findPropType(array $prop, array $setting, string $name, string $id): string|array {
    $first_not_null = function (iterable $it) use ($name, $id): mixed {
      foreach ($it as $value) {
        if ($value !== NULL) {
          return $value;
        }
      }
      throw new \Exception("Unable to find a component type for '$name' in '$id'.");
    };
    return $first_not_null((function () use ($prop, $setting, $name, $id): \Iterator {
      yield match ($prop['type'] ?? NULL) {
        'number' => match ($id) {
          'progress' => 'number',
          default => 'integer',
        },
        default => $prop['type'] ?? NULL,
      };
      yield match ($name) {
        'title_tag',
        'heading_tag',
        'subtitle_tag' => match ($setting['type'] ?? NULL) {
          'textfield',
          'text' => 'string',
          default => (function () {
            return NULL;
          })(),
        },
        'icon' => match ($id) {
          'alert' => 'string',
          default => ['object', 'string'],
        },
        default => NULL,
      };
      yield match ($prop['$ref'] ?? NULL) {
        // Unfortunately, a class like `Attribute::class` cannot be combined
        // with other types like an associative array or null.
        // Also, an Attribute object is cast to string for validation.
        'ui-patterns://attributes' => ['object', 'string'],
        NULL => NULL,
      };
      yield match ($setting['type'] ?? NULL) {
        'text',
        'markup',
        'render' => ['string', 'array', 'object'],
        'array' => 'array',
        'string' => 'string',
        // Object types.
        'object',
        'ImageValueObject',
        'FileValueObject' => 'object',
        'DateTime' => ['object', 'string'],
        'number' => 'integer',
        'mixed' => NULL,
        NULL => throw new \Exception("Missing type in '$name' setting or field in '$id' pattern."),
        default => throw new \Exception("Unexpected type '$setting[type]' in '$name' setting or field in '$id' pattern."),
      };
      yield match ($name) {
        'icon' => ['array', 'string'],
      };
    })());
  }

}
