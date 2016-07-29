<?php

namespace Tests\Helpers;

use Illuminate\Console\Command;
use Mnabialek\LaravelModular\Console\Traits\ModuleCreator as ModuleCreatorTrait;
use Mnabialek\LaravelModular\Models\Module;

class ModuleCreator extends Command
{
    use ModuleCreatorTrait;

    public function runVerifyStubGroup($stubGroup)
    {
        return $this->verifyStubGroup($stubGroup);
    }

    public function runVerifyConfigExistence()
    {
        return $this->verifyConfigExistence();
    }

    public function runGetStubGroup()
    {
        return $this->getStubGroup();
    }

    public function runGetFilesStubGroup()
    {
        return $this->getFilesStubGroup();
    }

    public function runCreateModuleDirectories(Module $module, $stubGroup)
    {
        return $this->createModuleDirectories($module, $stubGroup);
    }
}
