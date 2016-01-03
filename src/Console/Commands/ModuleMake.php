<?php

namespace Mnabialek\LaravelSimpleModules\Console\Commands;

use Exception;
use Mnabialek\LaravelSimpleModules\Console\Traits\ModuleCreator;
use Mnabialek\LaravelSimpleModules\Traits\Replacer;

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
        $modules = array_unique($this->argument('module'));

        $stubGroup = $this->getStubGroup();

        // verify whether stub directory exists
        $this->verifyStubGroup($stubGroup);

        foreach ($modules as $module) {
            // get module name (normalized or not)
            $module = $this->module->getModuleName($module);

            // module added to configuration or module directory exists
            if ($this->module->exists($module) ||
                $this->exists($this->module->getModuleDirectory($module))
            ) {
                $this->warn("[Module {$module}] already exists - ignoring");
            } else {
                // module does not exist - let's create it
                $this->createModule($module, $stubGroup);
                $this->info("[Module {$module}] Generated");
            }
        }
    }

    /**
     * Create module
     *
     * @param string $module
     * @param string $stubGroup
     */
    protected function createModule($module, $stubGroup)
    {
        $module = $this->module->getModuleName($module);

        $moduleDir = $this->module->getModuleDirectory($module);

        // first create directories
        $this->createModuleDirectories($module, $moduleDir, $stubGroup);

        // now create files
        $this->createModuleFiles($module, $moduleDir, $stubGroup);

        // finally add module to configuration (if not disabled in config) 
        $this->addModuleToConfigurationFile($module);
    }

    /**
     * Add module to configuration file
     *
     * @param $module
     */
    protected function addModuleToConfigurationFile($module)
    {
        if (!$this->module->config('module_make.auto_add')) {
            $this->info("[Module {$module}] - auto-adding to config file turned off\nPlease add this module manually into " .
                $this->module->getConfigFilePath() .
                ' file if you want to use it');

            return;
        }

        // getting modified content of config file
        $result =
            preg_replace_callback($this->module->config('module_make.pattern'),
                function ($matches) use ($module) {
                    $return = $matches[1] . $matches[2];

                    return $return .
                    $this->replace($this->module->config('module_make.module_template'),
                        $module) .
                    $matches[3];
                },
                file_get_contents($this->module->getConfigFilePath()), -1,
                $count);

        if ($count) {
            // found place where new module should be added into config file
            file_put_contents($this->module->getConfigFilePath(), $result);
            $this->comment("[Module {$module}] Added into config file " .
                $this->module->getConfigFilePath());
        } else {
            // cannot add module to config file automatically
            $this->warn("[Module {$module}] It was impossible to add module {$module} into " .
                $this->module->getConfigFilePath() .
                " file.\n Please make sure you haven't changed structure of this file. At the moment add <info>{$module}</info> to this file manually");
        }
    }
}
