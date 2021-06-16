<?php

declare(strict_types = 1);

namespace Drupal\oe_bootstrap_theme\TaskRunner\Commands;

use OpenEuropa\TaskRunner\Commands\AbstractCommands;
use Robo\Exception\TaskException;
use Symfony\Component\Console\Input\InputOption;

/**
 * Task runner commands for build steps.
 */
class BuildCommands extends AbstractCommands {

  /**
   * Runs an npm command in a given directory.
   *
   * @param string $npmCommand
   *   The npm command to run.
   * @param array $options
   *   Additional options for the command.
   *
   * @option npm-dir
   *   Path to the directory containing package.json, relative to project root.
   * @option purge
   *   If provided, the '/node_modules/' directory will be removed afterwards.
   *
   * @return \Robo\Collection\CollectionBuilder
   *   Collection builder with a list of tasks.
   *
   * @command oe_bootstrap_theme:npm-run
   *
   * @throws \Exception
   *   The task could not be constructed with the given parameters.
   */
  public function npmRun(string $npmCommand, array $options = [
    'npm-dir' => InputOption::VALUE_REQUIRED,
    'purge' => FALSE,
  ]) {
    $tasks = [];
    $npmDir = $this->parseNpmDirArg($options['npm-dir']);

    // Build npm command stack and add to tasks.
    $stack = $this->taskExecStack()
      ->dir($npmDir)
      ->stopOnFail();

    if (!file_exists($npmDir . '/package-lock.json')) {
      $stack->exec('npm install');
    }

    // If node_modules dir doesn't exist, run 'npm ci'.
    // See https://stackoverflow.com/questions/52499617/what-is-the-difference-between-npm-install-and-npm-ci
    if (!is_dir($npmDir . '/node_modules')) {
      $stack->exec('npm ci');
    }

    $stack->exec('npm run ' . $npmCommand);
    $tasks[] = $stack;

    // Delete npm artifacts if purge option is set.
    if ($options['purge']) {
      $nodeModulesDir = $npmDir . '/node_modules';
      $tasks[] = $this->taskDeleteDir($nodeModulesDir);
    }

    return $this->collectionBuilder()->addTaskList($tasks);
  }

  /**
   * Processes the --npm-dir parameter.
   *
   * @param string $npmDirArg
   *   Path to npm root, relative to project root.
   *   This can be untrusted user input from the --npm-dir parameter.
   *
   * @return string
   *   Absolute path to npm root.
   *
   * @throws \Exception
   *   Parameter value does not match the expected format.
   */
  protected function parseNpmDirArg($npmDirArg) {
    if (!$npmDirArg) {
      // The npm dir argument is missing or empty.
      throw new \InvalidArgumentException('Parameter --npm-dir is missing.');
    }

    if (!is_file($npmDirArg . '/package.json')) {
      throw new TaskException($this, sprintf('No package.json found in directory %s.', $npmDirArg));
    }

    return $npmDirArg;
  }

}
