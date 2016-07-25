<?php

namespace Mnabialek\LaravelModular\Console\Commands;

use Mnabialek\LaravelModular\Console\Traits\ModuleCreator;
use Mnabialek\LaravelModular\Console\Traits\ModuleVerification;
use Mnabialek\LaravelModular\Models\Module;

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
        $modules = collect($this->argument('module'))->unique();
        $modules = $this->verifyActive($modules);
        if ($modules === false || $modules->isEmpty()) {
            return;
        }

        $options = $this->getOptions();

        $modules->each(function ($module) use ($options) {
            /** @var Module $module */
            $class = $module->getSeederClass($this->option('class'));

            $result = $this->call('db:seed',
                array_merge($options, ['--class' => $class]));

            if ($result != 0) {
                $this->error("[Module {$module->getName()}] There was a problem with running seeder {$class}");

                return;
            }
            $this->info("[Module {$module->getName()}] Seeded: {$class}");
        });
    }
}
