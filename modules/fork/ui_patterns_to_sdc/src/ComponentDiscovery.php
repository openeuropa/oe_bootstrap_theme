<?php

declare(strict_types=1);

namespace Drupal\ui_patterns_to_sdc;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Extension\ExtensionPathResolver;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\File\FileSystemInterface;

/**
 * Plugin deriver for UI Patterns library.
 */
class ComponentDiscovery {

  /**
   * List of valid definition file extensions.
   *
   * @var array
   */
  public const array FILE_EXTENSIONS = [
    '.ui_patterns.yml',
    '.patterns.yml',
    '.pattern.yml',
  ];

  public function __construct(
    protected ExtensionPathResolver $pathResolver,
    protected ModuleHandlerInterface $moduleHandler,
    protected ThemeHandlerInterface $themeHandler,
    protected FileSystemInterface $filesystem,
  ) {}

  /**
   * Discover components.
   */
  public function discover(string $extension): array {
    $components = [];
    $path = $this->getExtensionPath($extension);
    foreach (\array_keys($this->fileScanDirectory($path)) as $file_path) {
      $content = \file_get_contents($file_path) ?: '';
      foreach (Yaml::decode($content) as $id => $definition) {
        $definition['id'] = $id;
        $definition['base path'] = \dirname($file_path);
        $definition['file name'] = \basename($file_path);
        $components[$file_path] = $definition;
      }
    }
    return $components;
  }

  /**
   * Get extension path.
   */
  public function getExtensionPath(string $extension): string {
    $type = $this->getExtensionType($extension);
    return $this->pathResolver->getPath($type, $extension);
  }

  /**
   * Does extension (theme or module) exists?
   */
  public function extensionExists(string $extension): bool {
    $type = $this->getExtensionType($extension);
    return match ($type) {
      'module' => $this->moduleHandler->moduleExists($extension),
      'theme' => $this->themeHandler->themeExists($extension),
      default => FALSE
    };
  }

  /**
   * Get extension type (theme or module).
   */
  protected function getExtensionType(string $extension): string {
    if ($this->moduleHandler->moduleExists($extension)) {
      return 'module';
    }
    return 'theme';
  }

  /**
   * Wrapper method for global function call.
   */
  protected function fileScanDirectory(string $directory): array {
    if (!\is_dir($directory)) {
      return [];
    }
    $options = ['nomask' => $this->getNoMask()];
    $extensions = self::FILE_EXTENSIONS;
    $extensions = \array_map('preg_quote', $extensions);
    $extensions = \implode('|', $extensions);
    $files = $this->filesystem->scanDirectory($directory, "/{$extensions}$/", $options);
    // In different file systems order of files in a folder can be different
    // that can break tests. So let's sort them alphabetically manually.
    \ksort($files);
    return $files;
  }

  /**
   * Returns a regular expression for directories to be excluded in a file scan.
   *
   * @return string
   *   Regular expression.
   */
  protected function getNoMask(): string {
    $ignore = [];
    // We add 'tests' directory to the ones found in settings.
    $ignore[] = 'tests';
    \array_walk($ignore, static function (&$value) {
      $value = \preg_quote($value, '/');
    });
    return '/^' . \implode('|', $ignore) . '$/';
  }

}
