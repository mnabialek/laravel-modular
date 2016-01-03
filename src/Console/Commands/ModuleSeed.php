<?php

namespace Mnabialek\LaravelSimpleModules\Console\Commands;

use Mnabialek\LaravelSimpleModules\Console\Traits\ModuleCreator;
use Mnabialek\LaravelSimpleModules\Console\Traits\ModuleVerification;

class ModuleSeed extends BaseCommand
{
    use ModuleVerification;
    use ModuleCreator;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:seed 
    {module* : Module name or multiple module names}
    {--class= : Class name that exists inside module seeds directory without namespace}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs main seeders for selected modules';

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
            $class =
                $this->module->getSeederClass($module, $this->option('class'));
            $result = $this->call('db:seed',
                array_merge($settings, ['--class' => $class]));

            if ($result != 0) {
                $this->error("[Module {$module}] There was a problem with running seeder {$class}");

                return;
            }
            $this->info("[Module {$module}] Seeded: {$class}");
        }
    }
}
