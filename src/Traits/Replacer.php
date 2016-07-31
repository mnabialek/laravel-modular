<?php

namespace Mnabialek\LaravelModular\Traits;

use Illuminate\Support\Collection;
use Mnabialek\LaravelModular\Models\Module;

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
            'module' => $module->getName(),
            'class' => $module->getName(),
            'moduleNamespace' => $module->getName(),
            'namespace' =>
                rtrim($this->laravel['config']->modulesNamespace(), '\\'),
            'plural|lower' => mb_strtolower(str_plural($module->getName())),
        ])->each(function ($value, $key) use ($replacements) {
            $replacements->put($this->laravel['config']->startSeparator() .
                $key . $this->laravel['config']->endSeparator(), $value);
        });

        return $replacements;
    }
}
