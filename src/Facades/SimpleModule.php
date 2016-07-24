<?php

namespace Mnabialek\LaravelModular\Facades;

use Illuminate\Support\Facades\Facade;

class Modular extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'modular';
    }
}
