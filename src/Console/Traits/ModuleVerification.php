<?php

namespace Mnabialek\LaravelSimpleModules\Console\Traits;

trait ModuleVerification
{
    /**
     * Verify whether given modules exist and are active
     *
     * @param array $modules
     *
     * @return array|bool
     */
    protected function verifyActive(array $modules)
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
     * @param array $modules
     *
     * @return array|bool
     */
    protected function verifyExisting(array $modules)
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
     * @param array $modules
     * @param bool $verifyActive
     *
     * @return array|bool
     */
    private function verifyModules(array $modules, $verifyActive)
    {
        $errors = false;

        $active = $this->module->active();
        $all = $this->module->all();

        foreach ($modules as $key => $module) {
            $moduleName = $this->module->getModuleName($module);

            if (!in_array($moduleName, $all)) {
                $errors = true;
                $this->error("Module {$moduleName} does not exist");
            } elseif ($verifyActive && !in_array($moduleName, $active)) {
                $errors = true;
                $this->error("Module {$moduleName} is not active");
            }
            $modules[$key] = $moduleName;
        }

        return ($errors) ? false : $modules;
    }
}
