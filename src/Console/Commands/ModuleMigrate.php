<?php

namespace Mnabialek\LaravelSimpleModules\Console\Commands;

use Mnabialek\LaravelSimpleModules\Console\Traits\ModuleCreator;
use Mnabialek\LaravelSimpleModules\Console\Traits\ModuleVerification;

class ModuleMigrate extends BaseCommand
{
    use ModuleVerification;
    use ModuleCreator;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:migrate 
    {module* : Module name (or multiple module names space separated)} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs all migration for selected modules';

    /**
     * {@inheritdoc}
     */
    public function proceed()
    {
        $modules = array_unique($this->argument('module'));
        $env = $this->option('env');

        $settings = [];
        if ($env) {
            $settings['--env'] = $env;
        }

        $modules = $this->verifyActive($modules);
        if ($modules === false) {
            return;
        }

        foreach ($modules as $module) {
            $modulePath = $this->module->getMigrationsPath($module);
            $result = $this->call('migrate',
                array_merge($settings, ['--path' => $modulePath]));

            if ($result != 0) {
                $this->error("[Module {$module}] There was a problem with running migrations from directory {$modulePath}");

                return;
            }
        }
    }
}
