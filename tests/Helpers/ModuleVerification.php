<?php

namespace Tests\Helpers;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Mnabialek\LaravelModular\Console\Traits\ModuleVerification as ModuleVerificationTrait;

class ModuleVerification extends Command
{
    use ModuleVerificationTrait;

    public function runVerifyActive(Collection $modules)
    {
        return $this->verifyActive($modules);
    }

    public function runVerifyExisting(Collection $modules)
    {
        return $this->verifyExisting($modules);
    }
}
