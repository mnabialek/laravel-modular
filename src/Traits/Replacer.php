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
    public function replace($string, Module $module, array $replacements = [])
    {
        $replacements = $this->getReplacements($module, $replacements);

        return str_replace($replacements->keys(), $replacements->values(),
            $string);
    }

    /**
     * Get replacement array that will be used for replace in string
     *
     * @param Module $module
     * @param array $definedReplacements
     *
     * @return Collection
     */
    protected function getReplacements(Module $module, array $definedReplacements)
    {
        $replacements = collect();

        collect($definedReplacements)->merge([
            'module' => $module->getName(),
            'class' => $module->getName(),
            'moduleNamespace' => $module->getName(),
            'namespace' => rtrim($this->config->getNamespace(), '\\'),
            'plural|lower' => mb_strtolower(str_plural($module->getName())),
        ])->each(function ($value, $key) use ($replacements) {
            $replacements->put($this->config->getStartSeparator() . $key .
                $this->config->getEndSeparator(), $value);
        });

        return $replacements;
    }
}
