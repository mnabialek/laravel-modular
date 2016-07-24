<?php

namespace Tests;

use Mockery as m;
use PHPUnit_Framework_TestCase;

class UnitTestCase extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }
}
