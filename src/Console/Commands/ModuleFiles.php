<?php

namespace Mnabialek\LaravelSimpleModules\Console\Commands;

use Mnabialek\LaravelSimpleModules\Console\Traits\ModuleCreator;
use Mnabialek\LaravelSimpleModules\Traits\Replacer;

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
        $module = $this->argument('module');
        $subModules = array_unique($this->argument('name'));
        $stubGroup = $this->getStubGroup('files_default_group');

        // verify whether stub directory exists
        $this->verifyStubGroup($stubGroup);

        $module = $this->module->getModuleName($module);

        if (!($this->module->exists($module))) {
            $this->error("Module {$module} does not exist. Run <comment>module:make {$module}</comment> command first to create it");

            return;
        }

        foreach ($subModules as $subModule) {
            // get submodule name (normalized or not)
            $subModule = $this->module->getModuleName($subModule);

            $this->createSubModule($module, $subModule, $stubGroup);
        }
    }

    /**
     * Create submodule
     *
     * @param string $module
     * @param string $subModule
     * @param string $stubGroup
     */
    protected function createSubModule($module, $subModule, $stubGroup)
    {
        $module = $this->module->getModuleName($module);

        $moduleDir = $this->module->getModuleDirectory($module);

        // first create directories
        $this->createModuleDirectories($module, $moduleDir, $stubGroup);

        // now create files
        $status = $this->createSubModuleFiles($module, $moduleDir, $stubGroup,
            $subModule);

        if ($status) {
            $this->info("[Module {$module}] Submodule {$subModule} created.");
            $this->comment("You should register submodule routes (if any) into routes file for module {$module}");
        } else {
            $this->warn("[Module {$module}] Submodule {$subModule} NOT created (all files already exist).");
        }
    }
}
