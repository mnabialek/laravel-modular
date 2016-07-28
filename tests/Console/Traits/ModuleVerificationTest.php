<?php

namespace Tests\Console\Traits;

use Exception;
use Mnabialek\LaravelModular\Models\Module;
use Mnabialek\LaravelModular\Services\Modular;
use Tests\Helpers\Application;
use Tests\Helpers\ModuleVerification;
use Mockery as m;
use Tests\UnitTestCase;

class ModuleVerificationTest extends UnitTestCase
{
    /** @test */
    public function it_throws_exception_when_given_modules_when_not_all_modules_are_active()
    {
        $app = m::mock(Application::class);
        $verification = m::mock(ModuleVerification::class)->makePartial();
        $modular = m::mock(Modular::class);

        $verification->setLaravel($app);

        $modules = collect(['A', 'B', 'C']);

        $app->shouldReceive('offsetGet')->times(3)->with('modular')
            ->andReturn($modular);
        $moduleA = m::mock(Module::class);
        $moduleC = m::mock(Module::class);
        
        $modular->shouldReceive('find')->with($modules[0])->andReturn($moduleA);
        $modular->shouldReceive('find')->with($modules[1])->andReturn(null);
        $modular->shouldReceive('find')->with($modules[2])->andReturn($moduleC);

        $moduleA->shouldReceive('isActive')->once()->withNoArgs()
            ->andReturn(true);
        $moduleC->shouldReceive('isActive')->once()->withNoArgs()
            ->andReturn(false);
        $verification->shouldReceive('error')->once()
            ->with('Module B does not exist');
        $verification->shouldReceive('error')->once()
            ->with('Module C is not active');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('There were errors. You need to pass only valid active module names');

        $verification->runVerifyActive($modules);
    }

    /** @test */
    public function it_returns_modules_when_given_modules_when_all_modules_are_active()
    {
        $app = m::mock(Application::class);
        $verification = m::mock(ModuleVerification::class)->makePartial();
        $modular = m::mock(Modular::class);

        $verification->setLaravel($app);

        $modules = collect(['A', 'C']);

        $app->shouldReceive('offsetGet')->times(2)->with('modular')
            ->andReturn($modular);
        $moduleA = m::mock(Module::class);
        $moduleC = m::mock(Module::class);

        $modular->shouldReceive('find')->with($modules[0])->andReturn($moduleA);
        $modular->shouldReceive('find')->with($modules[1])->andReturn($moduleC);

        $moduleA->shouldReceive('isActive')->once()->withNoArgs()
            ->andReturn(true);
        $moduleC->shouldReceive('isActive')->once()->withNoArgs()
            ->andReturn(true);
        $verification->shouldNotReceive('error');

        $result = $verification->runVerifyActive($modules);
        
        $this->assertEquals(collect([$moduleA, $moduleC]), $result);
    }

    /** @test */
    public function it_throws_exception_when_given_modules_when_not_all_modules_are_found()
    {
        $app = m::mock(Application::class);
        $verification = m::mock(ModuleVerification::class)->makePartial();
        $modular = m::mock(Modular::class);

        $verification->setLaravel($app);

        $modules = collect(['A', 'B', 'C']);

        $app->shouldReceive('offsetGet')->times(3)->with('modular')
            ->andReturn($modular);
        $moduleA = m::mock(Module::class);
        $moduleC = m::mock(Module::class);

        $modular->shouldReceive('find')->with($modules[0])->andReturn($moduleA);
        $modular->shouldReceive('find')->with($modules[1])->andReturn(null);
        $modular->shouldReceive('find')->with($modules[2])->andReturn($moduleC);

        $moduleA->shouldNotReceive('isActive');
        $moduleC->shouldNotReceive('isActive');
        $verification->shouldReceive('error')->once()
            ->with('Module B does not exist');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('There were errors. You need to pass only valid module names');

        $verification->runVerifyExisting($modules);
    }

    /** @test */
    public function it_returns_modules_when_given_modules_when_all_modules_are_found()
    {
        $app = m::mock(Application::class);
        $verification = m::mock(ModuleVerification::class)->makePartial();
        $modular = m::mock(Modular::class);

        $verification->setLaravel($app);

        $modules = collect(['A', 'C']);

        $app->shouldReceive('offsetGet')->times(2)->with('modular')
            ->andReturn($modular);
        $moduleA = m::mock(Module::class);
        $moduleC = m::mock(Module::class);

        $modular->shouldReceive('find')->with($modules[0])->andReturn($moduleA);
        $modular->shouldReceive('find')->with($modules[1])->andReturn($moduleC);

        $moduleA->shouldNotReceive('isActive');
        $moduleC->shouldNotReceive('isActive');
        $verification->shouldNotReceive('error');

        $result = $verification->runVerifyExisting($modules);

        $this->assertEquals(collect([$moduleA, $moduleC]), $result);
    }    
    
}
