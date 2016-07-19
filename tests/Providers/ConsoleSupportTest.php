<?php

use Illuminate\Foundation\Application;
use Mnabialek\LaravelSimpleModules\Providers\ConsoleSupport;
use Mockery as m;

class ConsoleSupportTest extends UnitTestCase
{
    /** @test */
    public function it_sets_valid_providers()
    {
        $dummyProviders = ['a', 'b', 'c'];

        $provider = m::mock(DummyConsoleSupportProvider::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $provider->shouldReceive('getCustomProviders')->once()
            ->andReturn($dummyProviders);

        $provider->__construct(m::mock(Application::class));
        $this->assertEquals($dummyProviders, $provider->providers());
    }

    /** @test */
    public function it_modifies_default_providers()
    {
        $replacingProviders = ['foo' => 'one', 'baz' => 'two'];

        $provider = m::mock(DummyConsoleSupportProvider::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $simpleModule = m::mock('stdClass');
        $simpleModule->shouldReceive('config')->once()->with('providers')
            ->andReturn($replacingProviders);

        $app = m::mock(DummyApp::class);
        $app->shouldReceive('offsetGet')->once()->with('simplemodule')
            ->andReturn($simpleModule);
        $provider->__construct($app);

        $this->assertEquals(['one', 'bar', 'two'], $provider->providers());
    }
}

// stubs

class DummyConsoleSupportProvider extends ConsoleSupport
{
    protected $providers = ['foo', 'bar', 'baz'];

    public function providers()
    {
        return $this->providers;
    }
}

class DummyApp extends Application implements ArrayAccess
{
}
