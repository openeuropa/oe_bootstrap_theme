<?php

declare(strict_types=1);

namespace Drupal\oe_bootstrap_theme_strict_sdc_validation;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\Core\Theme\Component\ComponentValidator;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Replaces the component validator with a stricter one.
 */
class OeBootstrapThemeStrictSdcValidationServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container): void {
    if (!$container->hasDefinition(ComponentValidator::class)) {
      return;
    }
    $container->getDefinition(ComponentValidator::class)
      ->setClass(StrictPropsComponentValidator::class)
      ->addMethodCall('setExtensionLists', [
        new Reference('extension.list.module'),
        new Reference('extension.list.theme'),
      ]);
  }

}
