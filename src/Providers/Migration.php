<?php

namespace Mnabialek\LaravelSimpleModules\Providers;

use Illuminate\Database\MigrationServiceProvider;

class Migration extends MigrationServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        parent::register();

        $this->setModulesMigrationPaths();
    }

    /**
     * Add migration paths for all active modules
     */
    protected function setModulesMigrationPaths()
    {
        $paths = collect();

        // add to paths all migration directories from modules
        collect($this->app['simplemodule']->active())
            ->each(function ($module) use ($paths) {
                $paths->push($this->app['simplemodule']->getMigrationsPath($module));
            });

        $this->loadMigrationsFrom($paths->all());
    }
}
