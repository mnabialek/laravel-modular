<?php

namespace Mnabialek\LaravelSimpleModules\Console\Traits;

use Illuminate\Support\Collection;

trait ModuleVerification
{
    /**
     * Verify whether given modules exist and are active
     *
     * @param array $modules
     *
     * @return array|bool
     */
    protected function verifyActive(Collection $modules)
    {
        $result = $this->verifyModules($modules, true);
        if (!$result) {
            $this->error("\nThere were errors. You need to pass only valid active module names");
        }

        return $result;
    }

    /**
     * Verify whether given modules exist
     *
     * @param Collection $modules
     *
     * @return array|bool
     */
    protected function verifyExisting(Collection $modules)
    {
        $result = $this->verifyModules($modules, false);
        if (!$result) {
            $this->error("\nThere were errors. You need to pass only valid module names");
        }

        return $result;
    }

    /**
     * Verifies whether given modules exist and whether they are active
     *
     * @param Collection $modules
     * @param bool $verifyActive
     *
     * @return array|bool
     */
    private function verifyModules(Collection $modules, $verifyActive)
    {
        $errors = false;
        
        $all = $this->module->all();
        
        $modules->each(function ($name) use ($all, $verifyActive, &$errors) {
            $found = $all->first(function ($module) use ($name) {
                return $module->getName() == $name;
            });
            
            if (!$found) {
                $errors = true;
                $this->error("Module {$name} does not exist");
            } elseif ($verifyActive && !$found->isActive()) {
                $errors = true;
                $this->error("Module {$name} is not active");
            };
        });

        return $errors ? false : $modules;
    }
}
