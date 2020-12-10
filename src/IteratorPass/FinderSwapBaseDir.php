<?php

namespace Drupal\eua_theme\IteratorPass;

use Symfony\Component\Finder\SplFileInfo;

/**
 * Iterator pass that rewrites the base path of each file.
 *
 * This can be used to find files that exist in two directories.
 * Should only be used with file iterators.
 */
class FinderSwapBaseDir implements IteratorPassInterface {

  /**
   * Base dir to replace.
   *
   * @var string
   */
  private $basedir;

  /**
   * Whether to require destination files to exist.
   *
   * @var bool
   */
  private $require;

  /**
   * Constructor.
   *
   * @param string $basedir
   *   Base dir to replace.
   * @param bool $require
   *   TRUE, to throw an exception if file does not exist in destination dir.
   *   FALSE, to skip such files.
   */
  public function __construct(string $basedir, bool $require = FALSE) {
    $this->basedir = $basedir;
    $this->require = $require;
  }

  /**
   * @param \Traversable $iterator
   *   Original iterator or traversable.
   *
   * @return \Iterator|\Symfony\Component\Finder\SplFileInfo[]
   *   Decorated files iterator.
   *
   * @throws \LogicException
   *   Decorated iterator is producing non-file values.
   * @throws \Exception
   *   Expected file not found in destination filesystem.
   */
  public function pass(\Traversable $iterator): \Iterator {
    foreach ($iterator as $key => $value) {
      if (!$value instanceof SplFileInfo) {
        throw new \LogicException("This iterator pass only works for file finders.");
      }
      $relpath = $value->getRelativePathname();
      $file = ($relpath === '')
        ? $this->basedir
        : $this->basedir . '/' . $relpath;
      if (!file_exists($file)) {
        if ($this->require) {
          throw new \Exception("Missing destination file: '$file'");
        }
        // Skip the missing file.
        continue;
      }
      yield $file => new SplFileInfo(
        $file,
        $value->getRelativePath(),
        $relpath);
    }
  }

}
