<?php

namespace Tests\Console\Facades;

use Mockery as m;
use Tests\UnitTestCase;
use Tests\Helpers\Application;
use Mnabialek\LaravelModular\Facades\Modular;

class ModularTest extends UnitTestCase
{
    /** @test */
    public function it_returns_valid_facade_accessor()
    {
        $app = m::mock(Application::class);
        Modular::setFacadeApplication($app);
        $app->shouldReceive('offsetGet')->once()->with('modular');
        Modular::getFacadeRoot();
    }
}
