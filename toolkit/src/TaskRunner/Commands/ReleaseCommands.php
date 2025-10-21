<?php

declare(strict_types=1);

namespace Drupal\oe_bootstrap_theme_commands\TaskRunner\Commands;

use EcEuropa\Toolkit\Task\Command\ConfigurationCommand;
use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use Symfony\Component\Console\Input\InputOption;

/**
 * Defines commands to create a release artifact.
 *
 * The artifact can be used by openeuropa/composer-artifacts.
 *
 * This class is based on similar code in openeuropa/task-runner.
 * Some design choices are intentionally kept aligned with the source.
 */
class ReleaseCommands extends AbstractCommands {

  /**
   * Create a release for the current project.
   *
   * This command creates a .tag.gz archive for the current project named as
   * follow:
   *
   * [PROJECT-NAME]-[CURRENT-TAG].[file-format]
   *
   * Where [file-format] can be tar.gz or zip, in case --zip option is used.
   *
   * @command toolkit:release:create-archive
   *
   * @option name Project name to use in the archive file name.
   * @option tag Release tag to use in the archive file name.
   * @option keep Keep the release directory, in addition to the archive.
   * @option zip Create *.zip instead of *.tar.gz archive.
   */
  public function createRelease(
    array $options = [
      'name' => InputOption::VALUE_REQUIRED,
      'tag' => InputOption::VALUE_REQUIRED,
      'keep' => FALSE,
      'zip' => FALSE,
    ],
  ) {
    $file_format = $options['zip'] ? 'zip' : 'tar.gz';
    $name = $options['name']
      // The InputOption::VALUE_REQUIRED is not reliable.
      // Throw an exception instead.
      ?? throw new \Exception('Missing --name parameter.');
    $version = $options['tag']
      ?? throw new \Exception('Missing --tag parameter.');
    $archive = "$name-$version." . $file_format;

    $tasks = [
      // Make sure we do not have a release directory yet.
      $this->taskFilesystemStack()->remove([$archive, $name]),

      // Get non-modified code using git archive.
      $this->taskGitStack()->exec(["archive", "HEAD", "-o $name.zip"]),
      $this->taskExtract("$name.zip")->to("$name"),
      $this->taskFilesystemStack()->remove("$name.zip"),
    ];

    // Append release tasks defined in runner.yml.dist.
    $release_tasks = $this->getConfig()->get("release.tasks");
    $tasks[] = $this->task(ConfigurationCommand::class, $release_tasks);

    // Create archive.
    if ($options['zip']) {
      $tasks[] = $this->taskExecStack()->exec("zip -r $archive $name");
    }
    else {
      $tasks[] = $this->taskExecStack()->exec("tar -czf $archive $name");
    }
    // Remove release directory, if not specified otherwise.
    if (!$options['keep']) {
      $tasks[] = $this->taskFilesystemStack()->remove($name);
    }

    return $this->collectionBuilder()->addTaskList($tasks);
  }

}
