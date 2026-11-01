<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel;

use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Render\Markup;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A form that is only used to give form context to render elements.
 */
class RenderWrapperForm implements FormInterface {

  public const FORM_CONTENT_SEPARATOR = '<!-- FORM_CONTENT_SEPARATOR -->';

  /**
   * Renders a render element in a form context.
   *
   * @param array $render
   *   A render element.
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   A service container.
   *
   * @return string
   *   The rendered html.
   */
  public static function renderInFormContext(array $render, ContainerInterface $container): string {
    // Wrap all the test structure inside a form. This will allow proper
    // processing of form elements and invocation of form alter hooks. Even if
    // the elements being tested are not form related, the form can host them
    // without causing any issues.
    $form_state = new FormState();
    $form_state->addBuildInfo('args', [$render]);
    $form_state->setProgrammed();

    $form_object = new static();
    $form = $container->get(FormBuilderInterface::class)
      ->buildForm($form_object, $form_state);
    $markup = $container->get(RendererInterface::class)
      ->renderRoot($form);
    return (string) $markup;
  }

  /**
   * Extracts rendered form content without the <form> wrapper element.
   *
   * @param string $html
   *   The original html which contains '<form>' wrapper.
   *
   * @return string
   *   The inner content that is relevant for the test.
   */
  public static function extractRenderedFormContent(string $html): string {
    $parts = explode(self::FORM_CONTENT_SEPARATOR, $html);
    if (count($parts) < 3) {
      return $html;
    }
    $inner_parts = array_slice($parts, 1, -1);
    return implode(self::FORM_CONTENT_SEPARATOR, $inner_parts);
  }

  /**
   * Builds the form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array|null $render_array
   *   The element render array.
   *
   * @return array
   *   The form render array.
   */
  public function buildForm(array $form, FormStateInterface $form_state, ?array $render_array = NULL): array {
    $form['before'] = ['#markup' => Markup::create(self::FORM_CONTENT_SEPARATOR)];
    $form['test'] = $render_array;
    $form['after'] = ['#markup' => Markup::create(self::FORM_CONTENT_SEPARATOR)];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    // Recurse through all the form elements and check if they have a property
    // "#set_validation_error". If they have, set a generic error on the
    // element.
    $add_errors = function (array $element) use (&$add_errors, $form_state): void {
      if (!empty($element['#set_validation_error'])) {
        // When the title is not present for a form element, fallback to its
        // path in the form.
        $label = !empty($element['#title']) ? $element['#title'] : implode('][', $element['#array_parents']);
        $form_state->setError($element, t('Validation error on @label', ['@label' => $label]));
      }

      foreach (Element::children($element) as $key) {
        // Recursively call this closure on all the children elements.
        $add_errors($element[$key]);
      }
    };

    $add_errors($form['test']);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {}

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'oe_bootstrap_theme_markup_rendering_test_form';
  }

}
