<?php

namespace Mnabialek\LaravelModular\Console\Traits;

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
     * @return bool|Collection
     * @throws \Exception
     */
    protected function verifyExisting(Collection $modules)
    {
        $result = $this->verifyModules($modules, false);
        if (!$result) {
            throw new \Exception("There were errors. You need to pass only valid module names");
        }

        return $result;
    }

    /**
     * Verifies whether given modules exist and whether they are active
     *
     * @param Collection $moduleNames
     * @param bool $verifyActive
     *
     * @return Collection|bool
     */
    private function verifyModules(Collection $moduleNames, $verifyActive)
    {
        $errors = false;
        
        $all = $this->module->all();
        
        $modules = collect();
        
        $moduleNames->each(function ($name) use ($all, $verifyActive, &$errors, $modules) {
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
            
            if (!$errors) {
                $modules->push($found);
            }
        });

        return $errors ? false : $modules;
    }
}
