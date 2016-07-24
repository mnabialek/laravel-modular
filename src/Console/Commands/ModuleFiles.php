<?php

namespace Mnabialek\LaravelSimpleModules\Console\Commands;

use Mnabialek\LaravelSimpleModules\Console\Traits\ModuleCreator;
use Mnabialek\LaravelSimpleModules\Models\Module;
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
        $subModules = collect($this->argument('name'))->unique();
        $stubGroup = $this->getFilesStubGroup();

        // verify whether stub directory exists
        $this->verifyStubGroup($stubGroup);

        if (!($this->module->exists($module))) {
            $this->error("Module {$module} does not exist. Run <comment>module:make {$module}</comment> command first to create it");

            return;
        }

        $module = new Module($module, [], $this->config);

        $subModules->each(function ($subModule) use ($module, $stubGroup) {
            $this->createSubModule($module, $subModule, $stubGroup);
        });

        foreach ($subModules as $subModule) {
            // get submodule name (normalized or not)
            $subModule = $this->module->getModuleName($subModule);

        }
    }

    /**
     * Create submodule
     *
     * @param string $module
     * @param string $subModule
     * @param string $stubGroup
     */
    protected function createSubModule(Module $module, $subModule, $stubGroup)
    {
        $module = $this->module->getModuleName($module);

        // first create directories
        $this->createModuleDirectories($module, $stubGroup);

        // now create files
        $status = $this->createSubModuleFiles($module, $subModule, $stubGroup);

        if ($status) {
            $this->info("[Module {$module}] Submodule {$subModule} created.");
            $this->comment("You should register submodule routes (if any) into routes file for module {$module}");
        } else {
            $this->warn("[Module {$module}] Submodule {$subModule} NOT created (all files already exist).");
        }
    }
}
