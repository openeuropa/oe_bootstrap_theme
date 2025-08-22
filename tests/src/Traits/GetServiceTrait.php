<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Traits;

/**
 * Provides a method to get a service by interface name.
 *
 * This allows static analysis without a `@var` doc.
 */
trait GetServiceTrait {

  /**
   * Gets a service from the container.
   *
   * @template TService of object
   *
   * @param class-string<TService> $type
   *   Expected class or interface for the service.
   * @param string|null $id
   *   Service id, or NULL to use $type as the service id.
   *
   * @return TService&object
   *   The service instance.
   */
  protected function service(string $type, ?string $id = NULL): object {
    $service = $this->container->get($id ?? $type);
    $this->assertInstanceOf($type, $service);
    return $service;
  }

}
