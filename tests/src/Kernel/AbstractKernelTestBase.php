<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel;

use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Site\Settings;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\oe_bootstrap_theme\Kernel\Traits\RenderTrait;
use Symfony\Component\Yaml\Yaml;

/**
 * Base class for theme's kernel tests.
 */
abstract class AbstractKernelTestBase extends KernelTestBase {

  use RenderTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'breakpoint',
    'image',
    'oe_bootstrap_theme_helper',
    'responsive_image',
    'system',
    'ui_patterns',
    'ui_patterns_settings',
    'ui_patterns_library',
    'user',
    'node',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installSchema('system', 'sequences');
    $this->installConfig(['user']);
    $this->installConfig([
      'system',
      'image',
      'responsive_image',
      'oe_bootstrap_theme_helper',
    ]);

    // Replicate 'file_scan_ignore_directories' from settings.php.
    $settings = Settings::getAll();
    $settings['file_scan_ignore_directories'] = [
      'node_modules',
      'bower_components',
      'vendor',
      'build',
    ];
    new Settings($settings);

    $this->container->get('theme_installer')->install(['oe_bootstrap_theme']);
    $this->config('system.theme')->set('default', 'oe_bootstrap_theme')->save();
    $this->container->set('theme.registry', NULL);

    // Call the install hook of the User module which creates the Anonymous user
    // and User 1. This is needed because the Anonymous user is loaded to
    // provide the current User context which is needed in places like route
    // enhancers.
    // @see CurrentUserContext::getRuntimeContexts().
    // @see EntityConverter::convert().
    $this->container->get('module_handler')->loadInclude('user', 'install');
    user_install();
  }

  /**
   * Get fixture content.
   *
   * @param string $filepath
   *   File path.
   *
   * @return array
   *   A set of test data.
   */
  protected function getFixtureContent(string $filepath): array {
    return Yaml::parse(file_get_contents(__DIR__ . "/fixtures/{$filepath}"));
  }

  /**
   * Builds and returns the renderable array for a block.
   *
   * @param string $block_id
   *   The ID of the block.
   * @param array $config
   *   An array of configuration.
   *
   * @return array
   *   A renderable array representing the content of the block.
   */
  protected function buildBlock(string $block_id, array $config): array {
    /** @var \Drupal\Core\Block\BlockBase $plugin */
    $plugin = $this->container->get('plugin.manager.block')->createInstance($block_id, $config);

    // Inject runtime contexts.
    if ($plugin instanceof ContextAwarePluginInterface) {
      $contexts = $this->container->get('context.repository')->getRuntimeContexts($plugin->getContextMapping());
      $this->container->get('context.handler')->applyContextMapping($plugin, $contexts);
    }

    return $plugin->build();
  }

}
