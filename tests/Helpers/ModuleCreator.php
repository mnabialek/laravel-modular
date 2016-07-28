<?php

namespace Tests\Helpers;

use Illuminate\Console\Command;
use Mnabialek\LaravelModular\Console\Traits\ModuleCreator as ModuleCreatorTrait;

class ModuleCreator extends Command 
{
    use ModuleCreatorTrait;

    public function runVerifyStubGroup( $stubGroup)
    {
        return $this->verifyStubGroup($stubGroup);
    }
}
