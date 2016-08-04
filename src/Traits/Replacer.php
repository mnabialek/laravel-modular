<?php

namespace Mnabialek\LaravelModular\Traits;

use Illuminate\Support\Collection;
use Mnabialek\LaravelModular\Models\Module;
use Mnabialek\LaravelModular\Services\Config;

trait Replacer
{
    /**
     * Replace given string with default replacements and optionally user given
     *
     * @param string $string
     * @param Module $module
     * @param array $replacements
     *
     * @return string
     */
    protected function replace($string, Module $module, array $replacements = [])
    {
        $replacements = $this->getReplacements($module, $replacements);

        return str_replace($replacements->keys()->all(),
            $replacements->values()->all(), $string);
    }

    /**
     * Get replacement array that will be used for replace in string
     *
     * @param Module $module
     * @param array $definedReplacements
     *
     * @return Collection
     */
    private function getReplacements(Module $module, array $definedReplacements)
    {
        $replacements = collect();

        collect($definedReplacements)->merge([
            'module' => $module->name(),
            'class' => $module->name(),
            'moduleNamespace' => $module->name(),
            'namespace' =>
                rtrim($this->configClass()->modulesNamespace(), '\\'),
            'plural|lower' => mb_strtolower(str_plural($module->name())),
        ])->each(function ($value, $key) use ($replacements) {
            $replacements->put($this->configClass()->startSeparator() .
                $key . $this->configClass()->endSeparator(), $value);
        });

        return $replacements;
    }

    /**
     * Get config class instance
     *
     * @return Config
     */
    private function configClass()
    {
        return $this->laravel['modular.config'];
    }
}
