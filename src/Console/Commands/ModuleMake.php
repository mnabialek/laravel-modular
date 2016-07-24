<?php

namespace Mnabialek\LaravelModular\Console\Commands;

use Exception;
use Mnabialek\LaravelModular\Console\Traits\ModuleCreator;
use Mnabialek\LaravelModular\Models\Module;
use Mnabialek\LaravelModular\Traits\Replacer;

class ModuleMake extends BaseCommand
{
    use Replacer;
    use ModuleCreator;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:make 
    {module* : Module name (or multiple module names space separated)} 
    {--group= : Stub group name that will be used for creating this module}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates new module structure.';

    /**
     * {@inheritdoc}
     */
    public function proceed()
    {
        $moduleNames = collect($this->argument('module'))->unique();

        $stubGroup = $this->getStubGroup();

        // verify whether stub directory exists
        $this->verifyStubGroup($stubGroup);

        $moduleNames->each(function ($moduleName) use ($stubGroup) {
            $this->module->find($moduleName);
            // module added to configuration or module directory exists

            $module = new Module($moduleName, [], $this->config);

            if ($this->module->exists($moduleName) ||
                $this->exists($module->getDirectory())
            ) {
                $this->warn("[Module {$moduleName}] already exists - ignoring");
                //throw new Exc
            } else {
                // module does not exist - let's create it
                $this->createModule($module, $stubGroup);
                $this->info("[Module {$module}] Generated");
            }
        });
    }

    /**
     * Create module
     *
     * @param Module $module
     * @param string $stubGroup
     */
    protected function createModule(Module $module, $stubGroup)
    {
        //$name = $module->getName();

        //$moduleDir = $module->getDirectory();

        // first create directories
        $this->createModuleDirectories($module, $stubGroup);

        // now create files
        $this->createModuleFiles($module, $stubGroup);

        // finally add module to configuration (if not disabled in config) 
        $this->addModuleToConfigurationFile($module);
    }

    /**
     * Add module to configuration file
     *
     * @param $module
     */
    protected function addModuleToConfigurationFile(Module $module)
    {
        if (!$this->config->autoAdd()) {
            $this->info("[Module {$module->getName()}] - auto-adding to config file turned off\nPlease add this module manually into " .
                $this->config->getConfigFilePath() .
                ' file if you want to use it');

            return;
        }

        $configFile = $this->config->getConfigFilePath();
        
        // getting modified content of config file
        $result = preg_replace_callback($this->config->autoAddPattern(),
            function ($matches) use ($module, $configFile) {
                return $matches[1] . $matches[2] .
                $this->replace($this->config->autoAddTemplate(), $module) .
                $matches[3];
            },
            file_get_contents($configFile), -1, $count);

        if ($count) {
            // found place where new module should be added into config file
            file_put_contents($configFile, $result);
            $this->comment("[Module {$module->getName()}] Added into config file {$configFile}");
        } else {
            // cannot add module to config file automatically
            $this->warn("[Module {$module->getaName()}] It was impossible to add module into {$configFile}" .
                " file.\n Please make sure you haven't changed structure of this file. " .
                "At the moment add <info>{$module->getName()}</info> to this file manually");
        }
    }
}
