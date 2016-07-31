<?php

namespace Tests\Helpers;

use Mnabialek\LaravelModular\Console\Commands\ModuleMake as BaseModuleMake;

class ModuleMake extends BaseModuleMake
{
    public function runCreateModuleObject($moduleName)
    {
        return $this->createModuleObject($moduleName);
    }
}
