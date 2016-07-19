<?php

use Illuminate\Foundation\Application;
use Mnabialek\LaravelSimpleModules\Providers\Migration;
use Mockery as m;

class MigrationTest extends UnitTestCase
{
    /** @test */
    public function it_calls_valid_functions_when_running_register_method()
    {
        $migration = m::mock(Migration::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $migration->shouldReceive('registerRepository')->once();
        $migration->shouldReceive('registerMigrator')->once();
        $migration->shouldReceive('registerCreator')->once();
        $migration->shouldReceive('registerCommands')->once();

        $migration->shouldReceive('setModulesMigrationPaths')->once();

        $migration->register();
    }

    /** @test */
    public function it_sets_valid_paths_for_migrations()
    {
        $activeModules = ['foo', 'bar', 'baz'];

        $paths = ['foopath', 'barpath', 'bazpath'];

        $app = m::mock(Application::class);

        $simpleModule = m::mock('stdClass');
        $simpleModule->shouldReceive('active')->once()
            ->andReturn($activeModules);
        $simpleModule->shouldReceive('getMigrationsPath')->once()
            ->with($activeModules[0])->andReturn($paths[0]);
        $simpleModule->shouldReceive('getMigrationsPath')->once()
            ->with($activeModules[1])->andReturn($paths[1]);
        $simpleModule->shouldReceive('getMigrationsPath')->once()
            ->with($activeModules[2])->andReturn($paths[2]);

        $app->shouldReceive('offsetGet')->times(4)->with('simplemodule')
            ->andReturn($simpleModule);

        $migration = m::mock(Migration::class, [$app])->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $migration->shouldReceive('registerRepository')->once();
        $migration->shouldReceive('registerMigrator')->once();
        $migration->shouldReceive('registerCreator')->once();
        $migration->shouldReceive('registerCommands')->once();

        $migration->shouldReceive('loadMigrationsFrom')->once()->with($paths);

        $migration->register();
    }
}

