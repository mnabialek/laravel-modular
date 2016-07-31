<?php

namespace Tests\Providers;

use Illuminate\Foundation\Application;
use Mnabialek\LaravelModular\Console\Commands\ModuleFiles;
use Mnabialek\LaravelModular\Console\Commands\ModuleMake;
use Mnabialek\LaravelModular\Console\Commands\ModuleMakeMigration;
use Mnabialek\LaravelModular\Console\Commands\ModuleSeed;
use Mnabialek\LaravelModular\Models\Module;
use Mnabialek\LaravelModular\Providers\ModularServiceProvider;
use Mnabialek\LaravelModular\Services\Config;
use Mnabialek\LaravelModular\Services\Modular;
use Mockery as m;
use Tests\UnitTestCase;

class ModularServiceProviderTest extends UnitTestCase
{
    /** @test */
    public function it_returns_valid_provides()
    {
        $modularProvider = m::mock(ModularServiceProvider::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->assertSame(['modular', 'modular.config'],
            $modularProvider->provides());
    }

    /** @test */
    public function it_does_all_required_things_when_registering()
    {
        $app = m::mock(Application::class);
        $config = m::mock(Config::class);

        $app->shouldReceive('make')->once()->with(Config::class)
            ->andReturn($config);

        $modularProvider = m::mock(ModularServiceProvider::class, [$app])
            ->makePartial()->shouldAllowMockingProtectedMethods();

        // module bindings
        $closure = m::on(function ($callback) use ($app) {
            return call_user_func($callback, $app) instanceof Modular;
        });
        $app->shouldReceive('singleton')->with('modular', $closure)->once();

        $closure2 = m::on(function ($callback) use ($app) {
            return call_user_func($callback, $app) instanceof Config;
        });
        $app->shouldReceive('singleton')->with('modular.config', $closure2)
            ->once();

        // Artisan commands
        $modularProvider->shouldReceive('commands')->once()->with([
            ModuleMake::class,
            ModuleSeed::class,
            ModuleMakeMigration::class,
            ModuleFiles::class,
        ]);

        // files to be published
        $stubsTemplatesPath =
            realpath(__DIR__ . '/../../stubs/templates/default');
        $stubsAppPath = realpath(__DIR__ . '/../../stubs/app/Core');
        $publishedStubsTemplatesPath = 'stubs/path';
        $publishedAppPath = 'app/path/';

        $from = [
            realpath(__DIR__ . '/../../config/modular.php'),
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
            'config/dir/modular.php',
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

        $modularProvider->shouldReceive('getFilesToPublish')->once()
            ->passthru();
        $modularProvider->shouldReceive('publishes')->once()
            ->with(array_combine($from, $to));

        $modular = m::mock('stdClass');

        // configuration file
        $config->shouldReceive('configName')->once()
            ->andReturn('modular');
        $config->shouldReceive('configPath')->once()
            ->andReturn($to[0]);

        // stubs files
        $modularProvider->shouldReceive('getTemplatesStubsPath')->once()
            ->passthru();
        $config->shouldReceive('stubsPath')->once()
            ->andReturn($publishedStubsTemplatesPath);

        // app files
        $modularProvider->shouldReceive('getAppSamplePath')->once()
            ->passthru();
        $app->shouldReceive('offsetGet')->times(2)->with('path')
            ->andReturn($publishedAppPath);

        // load migrations path
        $modules = [];
        $modules[] = m::mock(Module::class);
        $modules[0]->shouldReceive('getMigrationsPath')->once()
            ->andReturn('sample/module1/path');
        $modules[] = m::mock(Module::class);
        $modules[1]->shouldReceive('getMigrationsPath')->once()
            ->andReturn('sample/module2/path');

        $modularProvider->shouldReceive('setModulesMigrationPaths')->once()
            ->passthru();
        $modular->shouldReceive('active')->once()->andReturn(collect($modules));
        $modularProvider->shouldReceive('loadMigrationsFrom')
            ->with(['sample/module1/path', 'sample/module2/path']);

        // register modules providers
        $modular->shouldReceive('loadServiceProviders')->once();

        // usages of $this->app['modular']
        $app->shouldReceive('offsetGet')->times(2)->with('modular')
            ->andReturn($modular);

        $modularProvider->register();
    }
}
