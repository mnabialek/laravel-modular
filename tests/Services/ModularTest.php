<?php

namespace Test\Services;

use Illuminate\Database\Seeder;
use Illuminate\Routing\Router;
use Mnabialek\LaravelModular\Models\Module;
use Mnabialek\LaravelModular\Services\Config;
use Mnabialek\LaravelModular\Services\Modular;
use stdClass;
use Tests\Helpers\Application;
use Tests\UnitTestCase;
use Mockery as m;

class ModularTest extends UnitTestCase
{
    protected $app;
    protected $config;

    /**
     * @var Modular
     */
    protected $modular;

    public function setUp()
    {
        parent::setUp();
        $this->config = m::mock(Config::class);
        $this->app = m::mock(Application::class);
        $this->modular = m::mock(Modular::class, [$this->app, $this->config])
            ->makePartial()->shouldAllowMockingProtectedMethods();
    }

    /** @test */
    public function it_calls_valid_seeders()
    {
        $seed = m::mock(Seeder::class);
        $moduleA = m::mock(stdClass::class);
        $moduleB = m::mock(stdClass::class);
        $moduleA->shouldReceive('seederClass')->once()->withNoArgs()
            ->andReturn('seederAClass');
        $moduleB->shouldReceive('seederClass')->once()->withNoArgs()
            ->andReturn('seederBClass');

        $this->modular->shouldReceive('withSeeders')->once()
            ->andReturn(collect([$moduleA, $moduleB]));
        $seed->shouldReceive('call')->once()->with('seederAClass');
        $seed->shouldReceive('call')->once()->with('seederBClass');

        $this->assertEquals(null, $this->modular->seed($seed));
    }

    /** @test */
    public function it_loads_valid_routes()
    {
        $basePath = 'base/path';
        $moduleARouteFile = 'moduleA/routes.php';
        $moduleBRouteFile = 'moduleB/routes.php';

        $router = m::mock(Router::class);
        $moduleA = m::mock(stdClass::class);
        $moduleB = m::mock(stdClass::class);
        $moduleA->shouldReceive('routingControllerNamespace')->once()
            ->withNoArgs()
            ->andReturn('moduleAControllerNamespace');
        $moduleB->shouldReceive('routingControllerNamespace')->once()
            ->withNoArgs()
            ->andReturn('moduleBControllerNamespace');

        $this->modular->shouldReceive('withRoutes')->once()
            ->andReturn(collect([$moduleA, $moduleB]));
        $router->shouldReceive('group')->once()->with([
            'namespace' => 'moduleAControllerNamespace',
        ],
            m::on(function ($closure) use ($router) {
                call_user_func($closure, $router);

                return true;
            })
        );

        $this->app->shouldReceive('basePath')->times(2)->andReturn($basePath);
        $moduleA->shouldReceive('routesFilePath')->once()->withNoArgs()
            ->andReturn($moduleARouteFile);

        $file = m::mock(stdClass::class);

        $this->app->shouldReceive('offsetGet')->times(2)->with('files')
            ->andReturn($file);

        $file->shouldReceive('requireOnce')->once()->with($basePath .
            DIRECTORY_SEPARATOR . $moduleARouteFile);

        $router->shouldReceive('group')->once()
            ->with(['namespace' => 'moduleBControllerNamespace'],
                m::on(function ($closure) use ($router) {
                    call_user_func($closure, $router);

                    return true;
                }));

        $moduleB->shouldReceive('routesFilePath')->once()->withNoArgs()
            ->andReturn($moduleBRouteFile);

        $file->shouldReceive('requireOnce')->once()->with($basePath .
            DIRECTORY_SEPARATOR . $moduleBRouteFile);

        $this->assertEquals(null, $this->modular->loadRoutes($router));
    }

    /** @test */
    public function it_loads_valid_factories()
    {
        $moduleAFactoryFile = 'moduleA/factory.php';
        $moduleBFactoryFile = 'moduleB/factory.php';

        $moduleA = m::mock(stdClass::class);
        $moduleB = m::mock(stdClass::class);

        $this->modular->shouldReceive('withFactories')->once()
            ->andReturn(collect([$moduleA, $moduleB]));

        $file = m::mock(stdClass::class);

        $this->app->shouldReceive('offsetGet')->times(2)->with('files')
            ->andReturn($file);

        $moduleA->shouldReceive('factoryFilePath')->once()
            ->withNoArgs()->andReturn($moduleAFactoryFile);

        $file->shouldReceive('requireOnce')->once()->with($moduleAFactoryFile);

        $moduleB->shouldReceive('factoryFilePath')->once()
            ->withNoArgs()->andReturn($moduleBFactoryFile);

        $file->shouldReceive('requireOnce')->once()->with($moduleBFactoryFile);

        $this->assertEquals(null, $this->modular->loadFactories());
    }

    /** @test */
    public function it_loads_valid_service_providers()
    {
        $moduleAServiceProvider = 'moduleA/provider.php';
        $moduleBServiceProvider = 'moduleA/provider.php';

        $moduleA = m::mock(stdClass::class);
        $moduleB = m::mock(stdClass::class);

        $this->modular->shouldReceive('withServiceProviders')->once()
            ->andReturn(collect([$moduleA, $moduleB]));

        $moduleA->shouldReceive('serviceProviderClass')->once()
            ->withNoArgs()->andReturn($moduleAServiceProvider);

        $this->app->shouldReceive('register')->once()
            ->with($moduleAServiceProvider);

        $moduleB->shouldReceive('serviceProviderClass')->once()
            ->withNoArgs()->andReturn($moduleBServiceProvider);

        $this->app->shouldReceive('register')->once()
            ->with($moduleBServiceProvider);

        $this->assertEquals(null, $this->modular->loadServiceProviders());
    }

    /** @test */
    public function it_gets_only_active_modules_with_routes_when_requested()
    {
        $this->verifyValidFiltering('withRoutes', 'hasRoutes');
    }

    /** @test */
    public function it_gets_only_active_modules_with_factories_when_requested()
    {
        $this->verifyValidFiltering('withFactories', 'hasFactory');
    }

    /** @test */
    public function it_gets_only_active_modules_with_service_providers_when_requested()
    {
        $this->verifyValidFiltering('withServiceProviders',
            'hasServiceProvider');
    }

    /** @test */
    public function it_gets_only_active_modules_with_seeders_when_requested()
    {
        $this->verifyValidFiltering('withSeeders', 'hasSeeder');
    }

    protected function verifyValidFiltering($executeMethod, $filterMethod)
    {
        $moduleA = m::mock(stdClass::class);
        $moduleB = m::mock(stdClass::class);
        $moduleC = m::mock(stdClass::class);
        $moduleD = m::mock(stdClass::class);

        $this->modular->shouldReceive('filterActiveByMethod')->once()
            ->with($filterMethod)->passthru();

        $this->modular->shouldReceive('modules')->once()->withNoArgs()
            ->andReturn(collect([$moduleA, $moduleB, $moduleC, $moduleD]));

        $moduleA->shouldReceive('active')->once()->withNoArgs()
            ->andReturn(false);
        $moduleA->shouldNotReceive($filterMethod);

        $moduleB->shouldReceive('active')->once()->withNoArgs()
            ->andReturn(true);
        $moduleB->shouldReceive($filterMethod)->once()->andReturn(true);

        $moduleC->shouldReceive('active')->once()->withNoArgs()
            ->andReturn(true);
        $moduleC->shouldReceive($filterMethod)->once()->andReturn(false);

        $moduleD->shouldReceive('active')->once()->withNoArgs()
            ->andReturn(true);
        $moduleD->shouldReceive($filterMethod)->once()->andReturn(true);

        $this->assertEquals(collect([$moduleB, $moduleD]),
            $this->modular->$executeMethod());
    }

    /** @test */
    public function it_get_all_modules()
    {
        $this->modular->shouldReceive('modules')->times(2)->withNoArgs()
            ->passthru();

        $this->modular->shouldReceive('loadModules')->once()->withNoArgs()
            ->passthru();

        $modulesConfig = [
            'A' => ['routes' => true, 'providers' => false],
            'b' => ['routes' => true, 'providers' => true],
            'C' => ['routes' => true, 'providers' => true],
        ];

        $this->config->shouldReceive('modules')->once()->withNoArgs()
            ->andReturn($modulesConfig);

        $this->app->shouldReceive('offsetGet')->with('modular.config')
            ->andReturn($this->config);

        $moduleA = new Module('A', $this->app, $modulesConfig['A']);
        $moduleB = new Module('b', $this->app, $modulesConfig['b']);
        $moduleC = new Module('C', $this->app, $modulesConfig['C']);

        $this->assertEquals(collect([$moduleA, $moduleB, $moduleC]),
            $this->modular->all());

        // now let's make sure the result is exact same when we run it again
        // and we will also make sure this way that loadModules haven't been
        // run again
        $this->assertEquals(collect([$moduleA, $moduleB, $moduleC]),
            $this->modular->all());
    }

    /** @test */
    public function it_get_only_active_modules()
    {
        $moduleA = m::mock(Module::class);
        $moduleB = m::mock(Module::class);
        $moduleC = m::mock(Module::class);

        $this->modular->shouldReceive('modules')->once()->withNoArgs()
            ->andReturn(collect([$moduleA, $moduleB, $moduleC]));

        $moduleA->shouldReceive('active')->once()->withNoArgs()
            ->andReturn(true);
        $moduleB->shouldReceive('active')->once()->withNoArgs()
            ->andReturn(false);
        $moduleC->shouldReceive('active')->once()->withNoArgs()
            ->andReturn(true);

        $this->assertEquals(collect([$moduleA, $moduleC]),
            $this->modular->active());
    }

    /** @test */
    public function it_finds_valid_module_by_name_when_it_exists()
    {
        $moduleA = m::mock(Module::class);
        $moduleB = m::mock(Module::class);
        $moduleC = m::mock(Module::class);

        $moduleA->shouldReceive('name')->times(3)->withNoArgs()
            ->andReturn('foo');
        $moduleB->shouldReceive('name')->times(2)->withNoArgs()
            ->andReturn('bar');
        $moduleC->shouldReceive('name')->times(2)->withNoArgs()
            ->andReturn('baz');

        $this->modular->shouldReceive('modules')->times(3)->withNoArgs()
            ->andReturn(collect([$moduleA, $moduleB, $moduleC]));

        $this->assertEquals($moduleA, $this->modular->find('foo'));
        $this->assertSame(null, $this->modular->find('nothing'));
        $this->assertEquals($moduleC, $this->modular->find('baz'));
    }

    /** @test */
    public function it_return_valid_when_checking_module_existence_by_name()
    {
        $moduleA = m::mock(Module::class);

        $this->modular->shouldReceive('find')->once()->with('foo')
            ->andReturn($moduleA);
        $this->modular->shouldReceive('find')->once()->with('bar')
            ->andReturn(null);

        $this->assertSame(true, $this->modular->exists('foo'));
        $this->assertSame(false, $this->modular->exists('bar'));
    }
}
