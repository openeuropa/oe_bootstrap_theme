<?php

declare(strict_types=1);

namespace Drupal\oe_bootstrap_theme_sdc_port;

use Drupal\Component\Utility\Html;
use Drupal\Core\Render\Markup;
use Drupal\oe_bootstrap_theme_sdc_port\Twig\TwigGenerator;
use Drupal\ui_patterns\Element\PatternPreview;

/**
 * Generates and saves twig story files.
 *
 * This is different from StoryExtractor in ui_patterns v2:
 *   - The ui_patterns v2 creates yaml stories. Here we create twig stories.
 *   - Here we have access both to the processed pattern definition, and the new
 *     component definition.
 *
 * Also, this class has access to
 *
 * @see \Drupal\ui_patterns_to_sdc\StoryExtractor
 * @see \Drupal\oe_bootstrap_theme_sdc_port\ComponentWriterDecorator
 */
class TwigStoryWriter {

  public function __construct(
    private readonly TwigGenerator $twigGenerator,
  ) {}

  /**
   * Creates and saves a twig story for a component.
   *
   * @param array $target
   *   A generated component definition.
   * @param array $source
   *   A processed pattern definition.
   */
  public function createAndSaveStory(array $target, array $source): void {
    $component_id = $source['id'];
    $preview_values_by_variant = $this->collectPreviewValuesByVariant($source);
    $preview_values = $this->getPreviewValuesIfAllEqual($preview_values_by_variant);
    if ($preview_values !== NULL) {
      $twig_or_null = $this->buildTwigStoryFromPreviewTemplate($target, $source, $preview_values);
      $twig_or_null ??= $this->buildTwigStoryFromPreviewValues($component_id, $target, $preview_values);
    }
    else {
      $twig_or_null = $this->buildTwigStoryFromVariantPreviewValues($component_id, $target, $preview_values_by_variant);
    }
    $this->saveOrDeleteStory($component_id, $twig_or_null);
  }

  /**
   * Collects preview values for each variant.
   *
   * Some patterns have a preview preprocess function that alters the preview
   * values depending on the variant. This is why the values are collected
   * separately for each variant.
   *
   * @param array $source
   *   A processed pattern definition.
   *
   * @return array<string, array<string, mixed>>
   *   The preview values for slots and props, by variant name.
   *   If the pattern has no variants, the only key is ''.
   */
  protected function collectPreviewValuesByVariant(array $source): array {
    $component_id = $source['id'];
    $preview_values = $this->collectPreviewValuesFromDefinition($source);
    require_once dirname(__DIR__, 3) . '/includes/pattern.inc';
    $preprocess_function = 'oe_bootstrap_theme_preprocess_pattern_' . $component_id . '__preview';
    if (!function_exists($preprocess_function)) {
      return ['' => $preview_values];
    }
    $variant_names = array_keys($source['variants'] ?? []) ?: [''];
    $preview_values_by_variant = [];
    foreach ($variant_names as $variant_name) {
      $variant_preview_values = $preview_values + ['variant' => (string) $variant_name];
      $preprocess_function($variant_preview_values);
      unset($variant_preview_values['variant']);
      $preview_values_by_variant[$variant_name] = $variant_preview_values;
    }
    return $preview_values_by_variant;
  }

  /**
   * Gets the preview values, if they are the same for all variants.
   *
   * @param array<string, array<string, mixed>> $preview_values_by_variant
   *   Preview values by variant name.
   *
   * @return array<string, mixed>|null
   *   The common preview values, or NULL if they differ between variants.
   */
  protected function getPreviewValuesIfAllEqual(array $preview_values_by_variant): ?array {
    // Compare the generated twig expressions instead of the raw values.
    // This way, object instances with the same data are treated as equal, but
    // e.g. '123' and 123 are treated as different.
    $expressions = array_map(
      fn (array $values) => $this->twigGenerator->toTwigExpression($values),
      $preview_values_by_variant,
    );
    if (count(array_unique($expressions)) > 1) {
      return NULL;
    }
    return reset($preview_values_by_variant);
  }

  /**
   * Collects preview values from a pattern definition.
   *
   * @param array $source
   *   A processed pattern definition.
   *
   * @return array<string, mixed>
   *   The preview values for slots and props.
   */
  protected function collectPreviewValuesFromDefinition(array $source): array {
    $preview_values = [];
    foreach ($source['settings'] as $prop_name => $setting) {
      $prop_preview_value = $setting['preview'] ?? NULL;
      if ($prop_preview_value === NULL) {
        continue;
      }
      if (($setting['escape'] ?? TRUE) && $prop_preview_value !== '') {
        if (is_string($prop_preview_value)) {
          if (Html::escape($prop_preview_value) !== $prop_preview_value) {
            $prop_preview_value = Markup::create($prop_preview_value);
          }
        }
        elseif (is_array($prop_preview_value)) {
          // @phpstan-ignore staticMethod.deprecated
          $prop_preview_value = PatternPreview::getPreviewMarkup($prop_preview_value);
        }
      }
      $preview_values[$prop_name] = $prop_preview_value;
    }
    return $preview_values;
  }

  /**
   * Builds a story from a pattern preview template, if exists.
   *
   * The preview template is the "pattern-$name--preview.html.twig" template.
   *
   * @param array $target
   *   The component definition.
   * @param array $source
   *   The processed pattern definition.
   * @param array $preview_values
   *   Preview values that were extracted from the pattern definition.
   *
   * @return string|null
   *   The twig code for the story, or NULL if no preview template exists.
   */
  protected function buildTwigStoryFromPreviewTemplate(array $target, array $source, array $preview_values): ?string {
    $component_id = $source['id'];
    $twig = $this->loadPatternPreviewTemplate($component_id);

    // Abort, if no preview template is found.
    if ($twig === NULL) {
      return NULL;
    }

    // Replace pattern() calls with component includes.
    $twig = preg_replace(
      "#pattern\('(\w+)'#",
      "include_only('oe_bootstrap_theme:$1'",
      $twig,
    );

    // Repeat for every variant.
    if (!empty($target['variants'])) {
      $twig = $this->twigVariantsLoop($twig, $target['variants']);
    }

    // Set preview values as variables.
    if ($preview_values) {
      $twig = $this->twigGenerator->setTwigVariables($preview_values)
        . "\n" . $twig;
    }

    return $twig;
  }

  /**
   * Loads a pattern preview template.
   *
   * @param string $component_id
   *   The component id without the 'oe_bootstrap_theme:' prefix.
   *
   * @return string|null
   *   The twig content of the pattern preview template.
   */
  protected function loadPatternPreviewTemplate(string $component_id): ?string {
    $file = sprintf(
      '%s/templates/patterns/%s/pattern-%s--preview.html.twig',
      dirname(__DIR__, 3),
      $component_id,
      str_replace('_', '-', $component_id),
    );
    if (!file_exists($file)) {
      return NULL;
    }
    return file_get_contents($file);
  }

  /**
   * Builds a story from preview values, without a preview template.
   *
   * @param string $component_id
   *   The component id without the 'oe_bootstrap_theme:' prefix.
   * @param array $target
   *   The component definition.
   * @param array $preview_values
   *   Preview values that were extracted from the pattern definition.
   *
   * @return string
   *   The twig code.
   */
  protected function buildTwigStoryFromPreviewValues(string $component_id, array $target, array $preview_values): string {
    $component_arguments_expression = $this->twigGenerator->toTwigExpression($preview_values);

    if (!empty($target['variants'])) {
      // Also pass the variant in the argument array.
      assert(!array_key_exists('variant', $preview_values));
      $component_arguments_expression = preg_replace('#^\{#', "{\n  variant: variant,", $component_arguments_expression);
    }

    $twig = "{{ include_only('oe_bootstrap_theme:$component_id', $component_arguments_expression) }}\n";

    if (!empty($target['variants'])) {
      $twig = $this->twigVariantsLoop($twig, $target['variants']);
    }

    return $twig;
  }

  /**
   * Builds a story with separate preview values for each variant.
   *
   * This is used when a preview preprocess function produces different
   * preview values depending on the variant.
   *
   * @param string $component_id
   *   The component id without the 'oe_bootstrap_theme:' prefix.
   * @param array $target
   *   The component definition.
   * @param array<string, array<string, mixed>> $preview_values_by_variant
   *   Preview values by variant name.
   *
   * @return string
   *   The twig code.
   */
  protected function buildTwigStoryFromVariantPreviewValues(string $component_id, array $target, array $preview_values_by_variant): string {
    if ($this->loadPatternPreviewTemplate($component_id) !== NULL) {
      throw new \LogicException(sprintf(
        "Pattern '%s' has a preview template, and preview values that depend on the variant. This combination is not supported.",
        $component_id,
      ));
    }
    $twig = '';
    foreach ($target['variants'] as $variant_name => $variant) {
      $preview_values = ['variant' => (string) $variant_name] + $preview_values_by_variant[$variant_name];
      $component_arguments_expression = $this->twigGenerator->toTwigExpression($preview_values);
      $include_twig = "{{ include_only('oe_bootstrap_theme:$component_id', $component_arguments_expression) }}";
      $indented_twig = preg_replace('#^(.+)$#m', '  $1', $include_twig);
      $title = Html::escape((string) $variant['title']);
      $name = Html::escape((string) $variant_name);
      $twig .= <<<TWIG
<section class="my-4">
  <h3 class="my-3">$title (<code>$name</code>)</h3>
$indented_twig
</section>

TWIG;
    }
    return $twig;
  }

  /**
   * Repeats the same twig code over each variant.
   *
   * @param string $original_twig
   *   Original twig code.
   * @param array<string, array{title: string, description?: string} $variants
   *   List of variants of the component.
   *
   * @return string
   *   New twig code.
   */
  protected function twigVariantsLoop(string $original_twig, array $variants): string {
    $variant_titles = array_map(fn (array $variant) => $variant['title'], $variants);
    $variant_titles_expression = $this->twigGenerator->toTwigExpression($variant_titles);
    $indented_twig = preg_replace('#^(.+)$#m', '    $1', $original_twig);
    $indented_twig = rtrim($indented_twig);
    return <<<TWIG
{% for variant, variant_title in $variant_titles_expression %}
  <section class="my-4">
    <h3 class="my-3">{{ variant_title }} (<code>{{ variant }}</code>)</h3>
$indented_twig
  </section>
{% endfor %}
TWIG;
  }

  /**
   * Saves a story twig template, or deletes it if NULL is passed.
   *
   * @param string $component_id
   *   The component id without the 'oe_bootstrap_theme:' prefix.
   * @param string|null $twig
   *   The twig code for the story, or NULL to delete.
   */
  protected function saveOrDeleteStory(string $component_id, ?string $twig): void {
    $dir = dirname(__DIR__, 3) . '/components/' . $component_id;
    $file = $dir . '/' . $component_id . '.story.twig';
    if ($twig === NULL) {
      if (is_file($file)) {
        unlink($file);
      }
      return;
    }
    if (!is_dir($dir)) {
      mkdir($dir, 0775, TRUE);
    }
    file_put_contents($file, $twig);
  }

}
