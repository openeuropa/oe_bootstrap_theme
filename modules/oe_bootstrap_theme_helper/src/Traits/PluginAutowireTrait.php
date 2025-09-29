<?php

namespace Drupal\oe_bootstrap_theme_helper\Traits;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\AutowiringFailedException;

/**
 * Defines a trait for automatically wiring dependencies from the container.
 *
 * This trait uses reflection and may cause performance issues with classes
 * that will be instantiated multiple times.
 *
 * See https://www.drupal.org/project/drupal/issues/3452852
 */
trait PluginAutowireTrait {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $constructor = new \ReflectionMethod(static::class, '__construct');
    $args = [$configuration, $plugin_id, $plugin_definition];
    foreach (array_slice($constructor->getParameters(), count($args)) as $parameter) {
      $service = ltrim((string) $parameter->getType(), '?');
      foreach ($parameter->getAttributes(Autowire::class) as $attribute) {
        $service = (string) $attribute->newInstance()->value;
      }

      if (!$container->has($service)) {
        throw new AutowiringFailedException($service, sprintf('Cannot autowire service "%s": argument "$%s" of method "%s::_construct()", you should configure its value explicitly.', $service, $parameter->getName(), static::class));
      }

      $args[] = $container->get($service);
    }

    return new static(...$args);
  }

}
