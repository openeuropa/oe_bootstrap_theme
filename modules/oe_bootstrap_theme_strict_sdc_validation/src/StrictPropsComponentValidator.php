<?php

declare(strict_types=1);

namespace Drupal\oe_bootstrap_theme_strict_sdc_validation;

use Drupal\Core\Extension\ExtensionList;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Extension\ThemeExtensionList;
use Drupal\Core\Plugin\Component;
use Drupal\Core\Render\Component\Exception\InvalidComponentException;
use Drupal\Core\Theme\Component\ComponentValidator;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;

/**
 * Component validator that can validate props as declared.
 *
 * Core adds 'object' to the type of every top-level prop, and ignores type
 * errors for render arrays. As a result, any associative array, and any list
 * of render arrays, passes any prop.
 *
 * Extensions can opt in to stricter validation for their own components, with
 * this key in their *.info.yml file:
 *
 * @code
 * oe_bootstrap_theme_strict_sdc_validation: true
 * @endcode
 *
 * For components of such an extension, props are first validated against the
 * schema exactly as declared in the *.component.yml file, then by core.
 *
 * Like core validation, this only runs when PHP assertions are enabled.
 *
 * @see \Drupal\Core\Theme\Component\ComponentMetadata::parseSchemaInfo()
 */
class StrictPropsComponentValidator extends ComponentValidator {

  /**
   * The module extension list.
   */
  protected ModuleExtensionList $moduleExtensionList;

  /**
   * The theme extension list.
   */
  protected ThemeExtensionList $themeExtensionList;

  /**
   * Whether strict validation is enabled, keyed by provider.
   *
   * @var array<string, bool>
   */
  protected array $strictProviders = [];

  /**
   * Sets the extension lists.
   *
   * @param \Drupal\Core\Extension\ModuleExtensionList $module_extension_list
   *   The module extension list.
   * @param \Drupal\Core\Extension\ThemeExtensionList $theme_extension_list
   *   The theme extension list.
   */
  public function setExtensionLists(ModuleExtensionList $module_extension_list, ThemeExtensionList $theme_extension_list): void {
    $this->moduleExtensionList = $module_extension_list;
    $this->themeExtensionList = $theme_extension_list;
  }

  /**
   * {@inheritdoc}
   */
  public function validateProps(array $context, Component $component): bool {
    if ($this->isStrict($component->getPluginDefinition())) {
      $this->validateDeclaredSchema($context, $component);
    }
    return parent::validateProps($context, $component);
  }

  /**
   * Validates props against the schema as declared in the *.component.yml.
   *
   * Unlike core, this does not add 'object' to the prop types, and does not
   * ignore type errors for render arrays.
   *
   * @param array $context
   *   The context provided to the component.
   * @param \Drupal\Core\Plugin\Component $component
   *   The component.
   *
   * @throws \Drupal\Core\Render\Component\Exception\InvalidComponentException
   */
  protected function validateDeclaredSchema(array $context, Component $component): void {
    if (!$this->validator) {
      return;
    }
    $schema = $component->getPluginDefinition()['props'] ?? NULL;
    if (!is_array($schema) || empty($schema['properties'])) {
      return;
    }
    // Class types are not JSON schema types. Core checks them separately.
    foreach ($schema['properties'] as $name => $prop_schema) {
      foreach ((array) ($prop_schema['type'] ?? []) as $type) {
        if (str_contains((string) $type, '\\')) {
          unset($schema['properties'][$name]);
          break;
        }
      }
    }
    if (isset($schema['required'])) {
      $schema['required'] = array_values(array_intersect($schema['required'], array_keys($schema['properties'])));
    }
    // The context also contains slots and other variables.
    $props = array_intersect_key($context, $schema['properties']);

    $props_object = Validator::arrayToObjectRecursive($props);
    $this->validator->reset();
    $this->validator->validate(
      $props_object,
      Validator::arrayToObjectRecursive($schema),
      Constraint::CHECK_MODE_TYPE_CAST,
    );
    if ($this->validator->isValid()) {
      return;
    }
    $messages = array_map(
      static fn (array $error): string => sprintf('[%s] %s', $error['property'], $error['message']),
      $this->validator->getErrors(),
    );
    throw new InvalidComponentException(sprintf(
      "Strict validation failed for component \"%s\":\n%s",
      $component->getPluginId(),
      implode("\n", $messages),
    ));
  }

  /**
   * Checks whether the component's provider opted in to strict validation.
   *
   * @param array $definition
   *   The component plugin definition.
   *
   * @return bool
   *   TRUE if the provider opted in.
   */
  protected function isStrict(array $definition): bool {
    $provider = $definition['provider'] ?? NULL;
    if ($provider === NULL) {
      return FALSE;
    }
    if (!isset($this->strictProviders[$provider])) {
      // Core stores an internal string-backed enum here.
      $extension_type = $definition['extension_type'] ?? NULL;
      $extension_type = $extension_type instanceof \BackedEnum ? $extension_type->value : $extension_type;
      $extension_list = $extension_type === 'module'
        ? $this->moduleExtensionList
        : $this->themeExtensionList;
      $this->strictProviders[$provider] = $this->readStrictFlag($extension_list, $provider);
    }
    return $this->strictProviders[$provider];
  }

  /**
   * Reads the opt-in flag from an extension's info file.
   *
   * @param \Drupal\Core\Extension\ExtensionList $extension_list
   *   The extension list for the provider's type.
   * @param string $name
   *   The extension name.
   *
   * @return bool
   *   TRUE if the extension opted in.
   */
  protected function readStrictFlag(ExtensionList $extension_list, string $name): bool {
    if (!$extension_list->exists($name)) {
      return FALSE;
    }
    $info = $extension_list->getExtensionInfo($name);
    return !empty($info['oe_bootstrap_theme_strict_sdc_validation']);
  }

}
