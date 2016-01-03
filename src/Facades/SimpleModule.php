<?php

namespace Mnabialek\LaravelSimpleModules\Facades;

use Illuminate\Support\Facades\Facade;

class SimpleModule extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'simplemodule';
    }
}
