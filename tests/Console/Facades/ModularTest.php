<?php

namespace Tests\Console\Facades;

use Mnabialek\LaravelModular\Facades\Modular;
use Tests\Helpers\ApplicationClass;
use Tests\UnitTestCase;
use Mockery as m;

class ModularTest extends UnitTestCase
{
    /** @test */
    public function it_returns_valid_facade_accessor()
    {
        $app = m::mock(ApplicationClass::class);
        Modular::setFacadeApplication($app);
        $app->shouldReceive('offsetGet')->once()->with('modular');
        Modular::getFacadeRoot();
    }
}
