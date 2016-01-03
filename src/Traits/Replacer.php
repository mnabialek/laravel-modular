<?php

namespace Mnabialek\LaravelSimpleModules\Traits;

use Mnabialek\LaravelSimpleModules\SimpleModule;

trait Replacer
{
    /**
     * Replace given string with default replacements and optionally user given
     *
     * @param string $string
     * @param string $module
     * @param array $replacements
     *
     * @return string
     */
    public function replace($string, $module, array $replacements = [])
    {
        $replacements = $this->getReplacements($module, $replacements);

        return str_replace(array_keys($replacements),
            array_values($replacements),
            $string);
    }

    /**
     * Get replacement array that will be used for replace in string
     *
     * @param string $module
     * @param array $definedReplacements
     *
     * @return array
     */
    protected function getReplacements($module, array $definedReplacements)
    {
        $replacements = [];
        $simpleModule = $this->getSimpleModuleInstance();

        $definedReplacements = array_merge([
            'module' => $module,
            'class' => $module,
            'moduleNamespace' => $module,
            'namespace' => rtrim($simpleModule->config('namespace'), '\\'),
            'plural|lower' => mb_strtolower(str_plural($module)),
        ], $definedReplacements);

        foreach ($definedReplacements as $key => $val) {
            $replacements[$simpleModule->config('separators.start') . $key .
            $simpleModule->config('separators.end')] = $val;
        }

        return $replacements;
    }

    /**
     * Get SimpleModule instance
     *
     * @return SimpleModule
     * @throws \Exception
     */
    private function getSimpleModuleInstance()
    {
        if ($this instanceof SimpleModule) {
            return $this;
        }
        if (isset($this->module) && $this->module instanceof SimpleModule) {
            return $this->module;
        }

        throw new \Exception('Cannot resolve Module class');
    }
}
