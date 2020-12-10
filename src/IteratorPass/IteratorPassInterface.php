<?php

namespace Drupal\eua_theme\IteratorPass;

interface IteratorPassInterface {

  /**
   * @param \Traversable $iterator
   *   Original iterator or traversable.
   *
   * @return \Iterator
   *   Decorated iterator.
   *
   * @throws \Exception
   *   Sequence is interrupted or failed to start.
   */
  public function pass(\Traversable $iterator): \Iterator;

}
