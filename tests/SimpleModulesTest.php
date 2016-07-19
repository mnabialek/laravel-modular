<?php

use Illuminate\Foundation\Application;
use Mnabialek\LaravelSimpleModules\Console\Commands\ModuleFiles;
use Mnabialek\LaravelSimpleModules\Console\Commands\ModuleMake;
use Mnabialek\LaravelSimpleModules\Console\Commands\ModuleMakeMigration;
use Mnabialek\LaravelSimpleModules\Console\Commands\ModuleMigrate;
use Mnabialek\LaravelSimpleModules\Console\Commands\ModuleSeed;
use Mnabialek\LaravelSimpleModules\Providers\ConsoleSupport;
use Mnabialek\LaravelSimpleModules\Providers\SimpleModules;
use Mockery as m;

class SimpleModulesTest extends UnitTestCase
{
    /** @test */
    public function it_returns_valid_provides()
    {
        $simpleModule = m::mock(SimpleModules::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->assertEquals(['simplemodule'], $simpleModule->provides());
    }

    /** @test */
    public function it_does_all_required_things_when_registering()
    {
        $app = m::mock(Application::class);

        $simpleModules = m::mock(SimpleModules::class, [$app])->makePartial()
            ->shouldAllowMockingProtectedMethods();

        // module bindings
        $closure = m::on(function ($callback) use ($app) {
            call_user_func($callback, $app);

            return true;
        });

        $app->shouldReceive('bind')->with('simplemodule', $closure, true)
            ->once();

        // Artisan commands
        $simpleModules->shouldReceive('commands')->once()->with([
            ModuleMake::class,
            ModuleMigrate::class,
            ModuleSeed::class,
            ModuleMakeMigration::class,
            ModuleFiles::class,
        ]);

        // files to be published
        $stubsTemplatesPath = realpath(__DIR__ . '/../stubs/templates/default');
        $stubsAppPath = realpath(__DIR__ . '/../stubs/app/Core');
        $publishedStubsTemplatesPath = 'stubs/path';
        $publishedAppPath = 'app/path/';

        $from = [
            realpath(__DIR__ . '/../config/simplemodules.php'),
            $stubsTemplatesPath . DIRECTORY_SEPARATOR . 'Controller.php.stub',
            $stubsTemplatesPath . DIRECTORY_SEPARATOR .
            'DatabaseSeeder.php.stub',
            $stubsTemplatesPath . DIRECTORY_SEPARATOR . 'Exception.php.stub',
            $stubsTemplatesPath . DIRECTORY_SEPARATOR . 'migration.php.stub',
            $stubsTemplatesPath . DIRECTORY_SEPARATOR .
            'migration_create.php.stub',
            $stubsTemplatesPath . DIRECTORY_SEPARATOR .
            'migration_edit.php.stub',
            $stubsTemplatesPath . DIRECTORY_SEPARATOR . 'Model.php.stub',
            $stubsTemplatesPath . DIRECTORY_SEPARATOR . 'ModelFactory.php.stub',
            $stubsTemplatesPath . DIRECTORY_SEPARATOR . 'Repository.php.stub',
            $stubsTemplatesPath . DIRECTORY_SEPARATOR . 'Request.php.stub',
            $stubsTemplatesPath . DIRECTORY_SEPARATOR . 'routes.php.stub',
            $stubsTemplatesPath . DIRECTORY_SEPARATOR . 'Service.php.stub',
            $stubsTemplatesPath . DIRECTORY_SEPARATOR .
            'ServiceProvider.php.stub',
            $stubsTemplatesPath . DIRECTORY_SEPARATOR . '.gitkeep.stub',
            $stubsAppPath . DIRECTORY_SEPARATOR . 'AbstractRepository.php',
            $stubsAppPath . DIRECTORY_SEPARATOR . 'Service.php',

        ];
        $to = [
            'config/dir/simplemodules.php',
            $publishedStubsTemplatesPath . DIRECTORY_SEPARATOR . 'default/' .
            'Controller.php.stub',
            $publishedStubsTemplatesPath . DIRECTORY_SEPARATOR . 'default/' .
            'DatabaseSeeder.php.stub',
            $publishedStubsTemplatesPath . DIRECTORY_SEPARATOR . 'default/' .
            'Exception.php.stub',
            $publishedStubsTemplatesPath . DIRECTORY_SEPARATOR . 'default/' .
            'migration.php.stub',
            $publishedStubsTemplatesPath . DIRECTORY_SEPARATOR . 'default/' .
            'migration_create.php.stub',
            $publishedStubsTemplatesPath . DIRECTORY_SEPARATOR . 'default/' .
            'migration_edit.php.stub',
            $publishedStubsTemplatesPath . DIRECTORY_SEPARATOR . 'default/' .
            'Model.php.stub',
            $publishedStubsTemplatesPath . DIRECTORY_SEPARATOR . 'default/' .
            'ModelFactory.php.stub',
            $publishedStubsTemplatesPath . DIRECTORY_SEPARATOR . 'default/' .
            'Repository.php.stub',
            $publishedStubsTemplatesPath . DIRECTORY_SEPARATOR . 'default/' .
            'Request.php.stub',
            $publishedStubsTemplatesPath . DIRECTORY_SEPARATOR . 'default/' .
            'routes.php.stub',
            $publishedStubsTemplatesPath . DIRECTORY_SEPARATOR . 'default/' .
            'Service.php.stub',
            $publishedStubsTemplatesPath . DIRECTORY_SEPARATOR . 'default/' .
            'ServiceProvider.php.stub',
            $publishedStubsTemplatesPath . DIRECTORY_SEPARATOR . 'default/' .
            '.gitkeep.stub',
            $publishedAppPath . 'Core/' . 'AbstractRepository.php',
            $publishedAppPath . 'Core/' . 'Service.php',

        ];

        $simpleModules->shouldReceive('getFilesToPublish')->once()->passthru();
        $simpleModules->shouldReceive('publishes')->once()
            ->with(array_combine($from, $to));

        $simpleModule = m::mock('stdClass');

        // configuration file
        $simpleModule->shouldReceive('getConfigName')->once()
            ->andReturn('simplemodules');

        $simpleModule->shouldReceive('getConfigFilePath')->once()
            ->andReturn($to[0]);

        // stubs files
        $simpleModules->shouldReceive('getTemplatesStubsPath')->once()
            ->passthru();
        $simpleModule->shouldReceive('config')->once()->with('stubs.path')
            ->andReturn($publishedStubsTemplatesPath);

        // app files
        $simpleModules->shouldReceive('getAppSamplePath')->once()
            ->passthru();
        $app->shouldReceive('offsetGet')->times(2)->with('path')
            ->andReturn($publishedAppPath);

        // register modules providers
        $simpleModule->shouldReceive('loadServiceProviders')->once();

        // usages of $this->app['simplemodule']
        $app->shouldReceive('offsetGet')->times(4)->with('simplemodule')
            ->andReturn($simpleModule);

        $simpleModules->register();
    }
}


