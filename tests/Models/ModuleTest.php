<?php

namespace Tests\Models;

use Mnabialek\LaravelModular\Models\Module;
use Mnabialek\LaravelModular\Services\Config;
use stdClass;
use Tests\Helpers\Application;
use Tests\UnitTestCase;
use Mockery as m;

class ModuleTest extends UnitTestCase
{
    /**
     * @var  Module
     */
    protected $module;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var string
     */
    protected $name = 'Foo';

    /**
     * @var Application
     */
    protected $app;

    /** @test */
    public function it_returns_valid_module_name()
    {
        $this->createModuleMock();
        $this->assertSame($this->name, $this->module->name());
    }

    /** @test */
    public function it_returns_valid_seeder_class_when_no_class_given()
    {
        $this->createModuleMock();

        $this->module->shouldReceive('name')->once()->withNoArgs()->passthru();
        $this->config->shouldReceive('seederFile')->once()->withNoArgs()
            ->andReturn('some/path/Seeder.php');

        $this->config->shouldReceive('modulesNamespace')->once()->withNoArgs()
            ->andReturn('modules/namespace');
        $this->config->shouldReceive('seederNamespace')->once()->withNoArgs()
            ->andReturn('seeder/namespace');

        $this->module->shouldReceive('replace')->once()
            ->with('modules/namespace' .
                '\\' . $this->name . '\\' . 'seeder/namespace' . '\\' .
                'Seeder',
                m::on(function ($arg) {
                    return $arg instanceof Module && $arg->foo() == 'bar';
                }))->andReturn('result');

        $this->assertSame('result', $this->module->seederClass());
    }

    /** @test */
    public function it_returns_valid_seeder_class_when_class_given()
    {
        $this->createModuleMock();

        $this->module->shouldReceive('name')->once()->withNoArgs()->passthru();
        $this->config->shouldNotReceive('seederFilename');

        $this->config->shouldReceive('modulesNamespace')->once()->withNoArgs()
            ->andReturn('modules/namespace');
        $this->config->shouldReceive('seederNamespace')->once()->withNoArgs()
            ->andReturn('seeder/namespace');

        $this->module->shouldReceive('replace')->once()
            ->with('modules/namespace' .
                '\\' . $this->name . '\\' . 'seeder/namespace' . '\\' .
                'SampleClass',
                m::on(function ($arg) {
                    return $arg instanceof Module && $arg->foo() == 'bar';
                }))->andReturn('result');

        $this->assertSame('result',
            $this->module->seederClass('SampleClass'));
    }

    /** @test */
    public function it_returns_valid_module_directory()
    {
        $this->createModuleMock();
        $this->module->shouldReceive('name')->once()->withNoArgs()->passthru();

        $this->config->shouldReceive('directory')->once()->withNoArgs()
            ->andReturn('modules/namespace//');

        $this->assertSame('modules/namespace' . DIRECTORY_SEPARATOR .
            $this->name, $this->module->directory());
    }

    /** @test */
    public function it_returns_valid_module_migrations_path()
    {
        $this->createModuleMock();
        $this->module->shouldReceive('directory')->once()->withNoArgs()
            ->andReturn('modules/namespace');

        $this->config->shouldReceive('migrationsPath')->once()->withNoArgs()
            ->andReturn('db/migrations/path//');

        $this->assertSame('modules/namespace' . DIRECTORY_SEPARATOR .
            'db/migrations/path', $this->module->migrationsPath());
    }

    /** @test */
    public function it_returns_valid_service_provider_class()
    {
        $this->createModuleMock();

        $this->module->shouldReceive('name')->once()->withNoArgs()->passthru();
        $this->config->shouldReceive('serviceProviderFile')->once()
            ->withNoArgs()
            ->andReturn('some/path/ServiceProvider.php');

        $this->config->shouldReceive('modulesNamespace')->once()->withNoArgs()
            ->andReturn('modules/namespace');
        $this->config->shouldReceive('serviceProviderNamespace')->once()
            ->withNoArgs()
            ->andReturn('provider/namespace');

        $this->module->shouldReceive('replace')->once()
            ->with('modules/namespace' .
                '\\' . $this->name . '\\' . 'provider/namespace' . '\\' .
                'ServiceProvider', m::on(function ($arg) {
                return $arg instanceof Module && $arg->foo() == 'bar';
            }))->andReturn('result');

        $this->assertSame('result', $this->module->serviceProviderClass());
    }

    /** @test */
    public function it_returns_true_when_checking_provider_when_true_in_config()
    {
        $this->createModuleMock(['provider' => true]);
        $this->assertSame(true, $this->module->hasServiceProvider());
    }

    /** @test */
    public function it_returns_false_when_checking_provider_when_false_in_config()
    {
        $this->createModuleMock(['provider' => false]);
        $this->assertSame(false, $this->module->hasServiceProvider());
    }

    /** @test */
    public function it_returns_true_when_checking_provider_when_file_exists()
    {
        $this->createModuleMock([]);
        $file = m::mock(stdClass::class);

        $this->app->shouldReceive('offsetGet')->once()->with('files')
            ->andReturn($file);
        $this->module->shouldReceive('serviceProviderFilePath')->once()
            ->withNoArgs()
            ->andReturn('foo');
        $file->shouldReceive('exists')->once()->with('foo')->andReturn(true);

        $this->assertSame(true, $this->module->hasServiceProvider());
    }

    /** @test */
    public function it_returns_false_when_checking_provider_when_file_doesnt_exist()
    {
        $this->createModuleMock([]);
        $file = m::mock(stdClass::class);

        $this->app->shouldReceive('offsetGet')->once()->with('files')
            ->andReturn($file);
        $this->module->shouldReceive('serviceProviderFilePath')->once()
            ->withNoArgs()
            ->andReturn('foo');
        $file->shouldReceive('exists')->once()->with('foo')->andReturn(false);

        $this->assertSame(false, $this->module->hasServiceProvider());
    }

    /** @test */
    public function it_returns_true_when_checking_factory_when_true_in_config()
    {
        $this->createModuleMock(['factory' => true]);
        $this->assertSame(true, $this->module->hasFactory());
    }

    /** @test */
    public function it_returns_false_when_checking_factory_when_false_in_config()
    {
        $this->createModuleMock(['factory' => false]);
        $this->assertSame(false, $this->module->hasFactory());
    }

    /** @test */
    public function it_returns_true_when_checking_factory_when_file_exists()
    {
        $this->createModuleMock([]);
        $file = m::mock(stdClass::class);

        $this->app->shouldReceive('offsetGet')->once()->with('files')
            ->andReturn($file);
        $this->module->shouldReceive('factoryFilePath')->once()->withNoArgs()
            ->andReturn('foo');
        $file->shouldReceive('exists')->once()->with('foo')->andReturn(true);

        $this->assertSame(true, $this->module->hasFactory());
    }

    /** @test */
    public function it_returns_false_when_checking_factory_when_file_doesnt_exist()
    {
        $this->createModuleMock([]);
        $file = m::mock(stdClass::class);

        $this->app->shouldReceive('offsetGet')->once()->with('files')
            ->andReturn($file);
        $this->module->shouldReceive('factoryFilePath')->once()->withNoArgs()
            ->andReturn('foo');
        $file->shouldReceive('exists')->once()->with('foo')->andReturn(false);

        $this->assertSame(false, $this->module->hasFactory());
    }

    /** @test */
    public function it_returns_true_when_checking_routes_when_true_in_config()
    {
        $this->createModuleMock(['routes' => true]);
        $this->assertSame(true, $this->module->hasRoutes());
    }

    /** @test */
    public function it_returns_false_when_checking_routes_when_false_in_config()
    {
        $this->createModuleMock(['routes' => false]);
        $this->assertSame(false, $this->module->hasRoutes());
    }

    /** @test */
    public function it_returns_true_when_checking_routes_when_file_exists()
    {
        $this->createModuleMock([]);
        $file = m::mock(stdClass::class);

        $this->app->shouldReceive('offsetGet')->once()->with('files')
            ->andReturn($file);
        $this->module->shouldReceive('routesPath')->once()->withNoArgs()
            ->andReturn('foo');
        $file->shouldReceive('exists')->once()->with('foo')->andReturn(true);

        $this->assertSame(true, $this->module->hasRoutes());
    }

    /** @test */
    public function it_returns_false_when_checking_routes_when_file_doesnt_exist()
    {
        $this->createModuleMock([]);
        $file = m::mock(stdClass::class);

        $this->app->shouldReceive('offsetGet')->once()->with('files')
            ->andReturn($file);
        $this->module->shouldReceive('routesPath')->once()->withNoArgs()
            ->andReturn('foo');
        $file->shouldReceive('exists')->once()->with('foo')->andReturn(false);

        $this->assertSame(false, $this->module->hasRoutes());
    }

    /** @test */
    public function it_returns_true_when_checking_seeder_when_true_in_config()
    {
        $this->createModuleMock(['seeder' => true]);
        $this->assertSame(true, $this->module->hasSeeder());
    }

    /** @test */
    public function it_returns_false_when_checking_seeder_when_false_in_config()
    {
        $this->createModuleMock(['seeder' => false]);
        $this->assertSame(false, $this->module->hasSeeder());
    }

    /** @test */
    public function it_returns_true_when_checking_seeder_when_file_exists()
    {
        $this->createModuleMock([]);
        $file = m::mock(stdClass::class);

        $this->app->shouldReceive('offsetGet')->once()->with('files')
            ->andReturn($file);
        $this->module->shouldReceive('seederFilePath')->once()->withNoArgs()
            ->andReturn('foo');
        $file->shouldReceive('exists')->once()->with('foo')->andReturn(true);

        $this->assertSame(true, $this->module->hasSeeder());
    }

    /** @test */
    public function it_returns_false_when_checking_seeder_when_file_doesnt_exist()
    {
        $this->createModuleMock([]);
        $file = m::mock(stdClass::class);

        $this->app->shouldReceive('offsetGet')->once()->with('files')
            ->andReturn($file);
        $this->module->shouldReceive('seederFilePath')->once()->withNoArgs()
            ->andReturn('foo');
        $file->shouldReceive('exists')->once()->with('foo')->andReturn(false);

        $this->assertSame(false, $this->module->hasSeeder());
    }

    /** @test */
    public function it_returns_valid_routing_controller_namespace()
    {
        $this->createModuleMock([]);

        $this->config->shouldReceive('modulesNamespace')->once()->withNoArgs()
            ->andReturn('modules/namespace');
        $this->config->shouldReceive('routingControllerNamespace')->once()
            ->withNoArgs()->andReturn('routing/namespace');

        $this->module->shouldReceive('name')->once()->withNoArgs()
            ->andReturn($this->name);

        $this->assertSame('modules/namespace' . '\\' . $this->name . '\\' .
            'routing/namespace', $this->module->routingControllerNamespace());
    }

    /** @test */
    public function it_returns_valid_routes_file_path()
    {
        $this->verifyValidPath('routesFilePath', 'routingFile');
    }

    /** @test */
    public function it_returns_valid_factory_file_path()
    {
        $this->verifyValidPath('factoryFilePath', 'factoryFile');
    }

    /** @test */
    public function it_returns_valid_seeder_file_path()
    {
        $this->verifyValidPath('seederFilePath', 'seederFile');
    }

    /** @test */
    public function it_returns_valid_service_provider_file_path()
    {
        $this->verifyValidPath('serviceProviderFilePath',
            'serviceProviderFile');
    }

    protected function verifyValidPath($executionMethod, $configMethod)
    {
        $this->createModuleMock([]);

        $this->module->shouldReceive('getPath')->once()->with($configMethod)
            ->passthru();

        $this->config->shouldReceive($configMethod)->once()->withNoArgs()
            ->andReturn('routing/file.php');

        $this->module->shouldReceive('directory')->once()->withNoArgs()
            ->andReturn('module/dir');

        $this->module->shouldReceive('replace')->once()
            ->with('routing/file.php', m::on(function ($arg) {
                return $arg instanceof Module && $arg->foo() == 'bar';
            }))->andReturn('result');

        $this->assertSame('module/dir' . DIRECTORY_SEPARATOR .
            'result', $this->module->$executionMethod());
    }

    /** @test */
    public function it_returns_true_when_checking_active_when_true_in_config()
    {
        $this->createModuleMock(['active' => true]);
        $this->assertSame(true, $this->module->active());
    }

    /** @test */
    public function it_returns_false_when_checking_active_when_false_in_config()
    {
        $this->createModuleMock(['active' => false]);
        $this->assertSame(false, $this->module->active());
    }

    /** @test */
    public function it_returns_true_when_checking_active_when_file_exists()
    {
        $this->createModuleMock([]);
        $this->assertSame(true, $this->module->active());
    }

    protected function createModuleMock($options = [])
    {
        $this->app = m::mock(Application::class);
        $this->config = m::mock(Config::class);

        $this->app->shouldReceive('offsetGet')->once()->with('modular.config')
            ->andReturn($this->config);

        $this->module = m::mock(Module::class,
            [$this->name, $this->app, $options])
            ->makePartial()->shouldAllowMockingProtectedMethods();

        $this->module->shouldReceive('foo')->andReturn('bar');
    }
}
