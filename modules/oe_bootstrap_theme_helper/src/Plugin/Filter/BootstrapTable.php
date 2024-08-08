<?php

declare(strict_types=1);

namespace Drupal\oe_bootstrap_theme_helper\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * Provides a filter to add Bootstrap styling to tables.
 *
 * @Filter(
 *   id = "oe_bootstrap_theme_table",
 *   title = @Translation("Bootstrap tables"),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_IRREVERSIBLE,
 *   settings = {
 *     "responsive" = "",
 *   }
 * )
 */
final class BootstrapTable extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form['responsive'] = [
      '#type' => 'select',
      '#title' => $this->t('Responsive'),
      '#description' => $this->t('Makes the table responsive starting from the specified breakpoint. Choose "always" or "never" to turn on or off for all breakpoints.'),
      '#options' => [
        'always' => $this->t('Always'),
        'sm' => $this->t('Small'),
        'md' => $this->t('Medium'),
        'lg' => $this->t('Large'),
        'xl' => $this->t('Extra large'),
        'xxl' => $this->t('Extra extra large'),
      ],
      '#empty_option' => $this->t('Never'),
      '#default_value' => $this->settings['responsive'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);

    // Ensure that we have tables in the markup.
    if (stristr($text, '<table') === FALSE) {
      return $result;
    }

    $dom = Html::load($text);
    $xpath = new \DOMXPath($dom);

    // Prepare the class to use if responsive is enabled.
    $responsive = $this->settings['responsive'] !== '';
    if ($responsive) {
      $responsive_class = 'table-responsive';
      if ($this->settings['responsive'] !== 'always') {
        $responsive_class .= '-' . $this->settings['responsive'];
      }
    }

    foreach ($xpath->query('//table') as $table) {
      /** @var \DOMNode $table */
      $this->nodeAddClass($table, 'table');

      if (!$responsive) {
        continue;
      }

      // Do not add the responsive wrapper to nested tables.
      $parent_tables = $xpath->query('./ancestor::table', $table);
      if ($parent_tables->count()) {
        continue;
      }

      $wrapper = $dom->createElement('div');
      $this->nodeAddClass($wrapper, $responsive_class);
      $table->parentNode->replaceChild($wrapper, $table);
      $wrapper->appendChild($table);
    }

    $result->setProcessedText(Html::serialize($dom));

    return $result;
  }

  /**
   * Adds a class to a DOM node.
   *
   * @param \DOMNode $node
   *   Element.
   * @param string $class
   *   Class that should be added.
   */
  public function nodeAddClass(\DOMNode $node, string $class): void {
    $classes = array_filter(array_map('trim', explode(' ', $node->getAttribute('class'))));

    if (in_array($class, $classes)) {
      return;
    }

    $classes[] = $class;
    $node->setAttribute('class', implode(' ', $classes));
  }

}
