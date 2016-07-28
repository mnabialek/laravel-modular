<?php

namespace Mnabialek\LaravelModular\Console\Commands;

use Mnabialek\LaravelModular\Console\Traits\ModuleCreator;
use Mnabialek\LaravelModular\Models\Module;
use Mnabialek\LaravelModular\Traits\Replacer;

class ModuleFiles extends BaseCommand
{
    use Replacer;
    use ModuleCreator;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:files 
    {module : Module name}
    {name* : Name (or multiple names space separated)}
    {--group= : Stub group name that will be used for creating those files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates new files structure in existing module.';

    /**
     * {@inheritdoc}
     */
    public function proceed()
    {
        $moduleName = $this->argument('module');
        $subModules = collect($this->argument('name'))->unique();
        $stubGroup = $this->getFilesStubGroup();

        $this->verifyStubGroup($stubGroup);

        if (!($module = $this->laravel['modular']->find($moduleName))) {
            $this->error("[Module {$moduleName}] This module does not exist. Run <comment>module:make {$moduleName}</comment> command first to create it");

            return;
        }

        $subModules->each(function ($subModule) use ($module, $stubGroup) {
            $this->createSubModule($module, $subModule, $stubGroup);
        });
    }

    /**
     * Create submodule for given module
     *
     * @param Module $module
     * @param string $subModule
     * @param string $stubGroup
     */
    protected function createSubModule(Module $module, $subModule, $stubGroup)
    {
        // first create directories
        $this->createModuleDirectories($module, $stubGroup);

        // now create files
        $status = $this->createModuleFiles($module, $subModule, $stubGroup);

        if ($status) {
            $this->info("[Module {$module->getName()}] Submodule {$subModule} was created.");
            $this->comment("You should register submodule routes (if any) into routes file for module {$module->getName()}");
        } else {
            $this->warn("[Module {$module->getName()}] Submodule {$subModule} NOT created (all files already exist).");
        }
    }
}
