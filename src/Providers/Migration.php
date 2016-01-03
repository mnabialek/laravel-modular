<?php

namespace Mnabialek\LaravelSimpleModules\Providers;

use Illuminate\Database\MigrationServiceProvider;
use Mnabialek\LaravelSimpleModules\Extensions\Migrator;

class Migration extends MigrationServiceProvider
{
    /**
     * Here we register custom migrator
     */
    protected function registerMigrator()
    {
        // The migrator is responsible for actually running and rollback the migration
        // files in the application. We'll pass in our database connection resolver
        // so the migrator can resolve any of these connections when it needs to.
        $this->app->singleton('migrator', function ($app) {
            $repository = $app['migration.repository'];

            return new Migrator($repository, $app['db'], $app['files']);
        });
    }
}
