<?php

namespace Drupal\oe_bootstrap_theme;

use Drupal\Core\Config\FileStorage;
use Drupal\Component\Utility\Crypt;

/**
 * Utility class with static methods related to configuration stuff.
 */
class ConfigUtil {

  /**
   * Installs configuration for paragraphs from file.
   *
   * @param array $configuration_names
   *   Contains the name of configuration files that must be installed.
   * @param string $folder
   *   Contains the folder where the config resides.
   */
  public static function importConfigFromFile(array $configuration_names, string $folder): void {
    $storage = new FileStorage(drupal_get_path('module', 'oe_bootstrap_theme_paragraphs') . $folder);
    $config_manager = \Drupal::service('config.manager');
    $entity_type_manager = \Drupal::entityTypeManager();

    if (isset($configuration_names['fields'])) {
      // Clear the field widget cache.
      \Drupal::service('plugin.manager.field.widget')->clearCachedDefinitions();

      foreach ($configuration_names['fields'] as $config_name) {
        $config_record = $storage->read($config_name);
        $entity_type = $config_manager->getEntityTypeIdByName($config_name);
        /** @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface $entity_storage */
        $entity_storage = $entity_type_manager->getStorage($entity_type);
        $entity = $entity_storage->createFromStorageRecord($config_record);
        $entity->save();
      }
    }

    if (isset($configuration_names['displays'])) {
      foreach ($configuration_names['displays'] as $name) {
        $configuration = $storage->read($name);
        if (!$configuration) {
          throw new \LogicException(sprintf('The configuration value named %s was not found in the storage.', $name));
        }

        $entity_type = $config_manager->getEntityTypeIdByName($name);
        /** @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface $entity_storage */
        $entity_storage = $entity_type_manager->getStorage($entity_type);
        $id_key = $entity_storage->getEntityType()->getKey('id');
        $entity = $entity_storage->load($configuration[$id_key]);
        /* When we create a new config, it usually means that we are also ship-
        ping it in the config/install folder, so we must ensure it gets the hash
        so Drupal treats it as a shipped conf. This means that it gets exposed
        to be translated via the locale system as well. */
        $configuration['_core']['default_config_hash'] = Crypt::hashBase64(serialize($configuration));
        $entity = $entity_storage->updateFromStorageRecord($entity, $configuration);
        $entity->save();
      }
    }

  }

}
