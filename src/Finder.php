<?php

namespace Drupal\eua_theme;

use Drupal\eua_theme\IteratorPass\FinderSwapBaseDir;
use Drupal\eua_theme\IteratorPass\IteratorPassInterface;
use Symfony\Component\Finder\Finder as SymfonyFinder;

/**
 * Finder with additional functionality:
 */
class Finder extends SymfonyFinder {

  /**
   * Iterator passes to apply to the iterator.
   *
   * @var \Drupal\eua_theme\IteratorPass\IteratorPassInterface[]
   */
  private $iteratorPasses = [];

  /**
   * Swaps the base directory.
   *
   * This can be used to find files that exist in two directories.
   *
   * @param string $basedir
   *   Base dir to replace.
   * @param bool $require
   *   TRUE, to throw an exception if file does not exist in destination dir.
   *   FALSE, to skip such files.
   *
   * @return $this
   */
  public function swapBase(string $basedir, bool $require = FALSE) {
    return $this->addIteratorPass(
      new FinderSwapBaseDir($basedir, $require));
  }

  /**
   * Adds an iterator pass.
   *
   * @param \Drupal\eua_theme\IteratorPass\IteratorPassInterface $iteratorPass
   *   Iterator pass.
   *
   * @return $this
   */
  public function addIteratorPass(IteratorPassInterface $iteratorPass) {
    $this->iteratorPasses[] = $iteratorPass;
    return $this;
  }

  /**
   * Returns an Iterator for the current Finder configuration.
   *
   * This does not use 'inheritdoc' because:
   * - Declare additional exception type.
   * - Easier for IDE to predict item type.
   *
   * @return \Iterator|\Symfony\Component\Finder\SplFileInfo[]
   *   Files iterator.
   *
   * @throws \LogicException
   *   Object is incomplete, because the ->in() method has not been called.
   * @throws \Exception
   *   Iterator failed to start or was interrupted.
   */
  public function getIterator() {
    $iterator = parent::getIterator();
    foreach ($this->iteratorPasses as $iteratorPass) {
      $iterator = $iteratorPass->pass($iterator);
    }
    return $iterator;
  }

}
