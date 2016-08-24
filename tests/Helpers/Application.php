<?php

namespace Tests\Helpers;

use ArrayAccess;
use Illuminate\Foundation\Application as BaseApplication;

class Application extends BaseApplication implements ArrayAccess
{
    public function offsetExists($offset)
    {
    }

    public function offsetGet($offset)
    {
    }

    public function offsetSet($offset, $value)
    {
    }

    public function offsetUnset($offset)
    {
    }
}
