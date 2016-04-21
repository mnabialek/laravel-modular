<?php

namespace Mnabialek\LaravelSimpleModules\Extensions;

use Illuminate\Database\Migrations\Migrator as BaseMigrator;
use SimpleModule;

class Migrator extends BaseMigrator
{
    /**
     * Run the outstanding migrations at a given path.
     *
     * @param  string $path
     * @param  array $options
     *
     * @return void
     */
    public function run($path, array $options = [])
    {
        $this->notes = [];

        $files = $this->getMigrationFilesWithPaths($path);

        // Once we grab all of the migration files for the path, we will compare them
        // against the migrations that have already been run for this package then
        // run each of the outstanding migrations against a database connection.
        $ran = $this->repository->getRan();

        // get names of migrations that have not been run yet
        $migrations = array_diff(array_keys($files), $ran);

        // run all migrations that have not been run yet
        foreach ($migrations as $migration) {
            $this->files->requireOnce($files[$migration] . DIRECTORY_SEPARATOR .
                $migration . '.php');
        }

        $this->runMigrationList($migrations, $options);
    }

    /**
     * Rollback the last migration operation.
     *
     * @param  bool $pretend
     *
     * @return int
     */
    public function rollback($pretend = false)
    {
        $this->notes = [];

        // We want to pull in the last batch of migrations that ran on the previous
        // migration operation. We'll then reverse those migrations and run each
        // of them "down" to reverse the last migration "operation" which ran.
        $migrations = $this->repository->getLast();

        $count = count($migrations);

        // get all migration files
        $files = $this->getMigrationFilesWithPaths(database_path('migrations'),
            true);

        if ($count === 0) {
            $this->note('<info>Nothing to rollback.</info>');
        } else {
            // We need to reverse these migrations so that they are "downed" in reverse
            // to what they run on "up". It lets us backtrack through the migrations
            // and properly reverse the entire database schema operation that ran.
            foreach ($migrations as $migration) {
                // first we require migration file
                $this->files->requireOnce($files[$migration->migration] .
                    DIRECTORY_SEPARATOR .
                    $migration->migration . '.php');

                // then we rollback migration
                $this->runDown((object)$migration, $pretend);
            }
        }

        return $count;
    }

    /**
     * Rolls all of the currently applied migrations back.
     *
     * @param  bool $pretend
     *
     * @return int
     */
    public function reset($pretend = false)
    {
        $this->notes = [];

        $migrations = array_reverse($this->repository->getRan());

        // get all migration files
        $files = $this->getMigrationFilesWithPaths(database_path('migrations'),
            true);

        $count = count($migrations);

        if ($count === 0) {
            $this->note('<info>Nothing to rollback.</info>');
        } else {
            foreach ($migrations as $migration) {
                // first we require migration file
                $this->files->requireOnce($files[$migration] .
                    DIRECTORY_SEPARATOR . $migration . '.php');

                // then we rollback migration
                $this->runDown((object)['migration' => $migration], $pretend);
            }
        }

        return $count;
    }

    /**
     * Get all migration files
     *
     * @param string $path
     * @param bool $reverse
     *
     * @return array
     */
    public function getMigrationFiles($path, $reverse = false)
    {
        return array_keys($this->getMigrationFilesWithPaths($path, $reverse));
    }

    /**
     * Get migration files together with paths
     *
     * @param string|array $path
     * @param bool $reverse
     *
     * @return array
     */
    protected function getMigrationFilesWithPaths($path, $reverse = false)
    {
        $paths = (array)$path;
        $defaultPath = database_path('migrations');

        // add modules paths only if no path given or given path is equal to
        // default Laravel path. Otherwise it means user wants to run migration
        // from custom location and we don't want to add other modules in that
        // case
        if ((realpath($path) == realpath($defaultPath)) || !$path) {
            // get active modules list
            $modules = SimpleModule::active();

            // add to paths all migration directories from modules
            foreach ($modules as $module) {
                $paths[] = SimpleModule::getMigrationsPath($module, false);
            }
        }

        return $this->findMigrationFiles($paths, $reverse);
    }

    /**
     * Find migration files
     *
     * @param array $paths
     * @param $reverse
     *
     * @return array
     */
    protected function findMigrationFiles(array $paths, $reverse)
    {
        // function to get file names without extensions
        $fileNameWithoutExtension = function ($file) {
            return basename($file, '.php');
        };

        foreach ($paths as $path) {
            $files = $this->files->glob($path . '/*_*.php');

            if ($files === false) {
                continue;
            }

            $files = array_map($fileNameWithoutExtension, $files);

            foreach ($files as $file) {
                $list[$file] = $path;
            }
        }

        // no migrations - nothing to do
        if (!isset($list)) {
            return [];
        }

        // now we need to sort migrations (all starts with timestamp) - either
        // in ascending order or descending
        if ($reverse === false) {
            ksort($list);
        } else {
            krsort($list);
        }

        return $list;
    }
}
