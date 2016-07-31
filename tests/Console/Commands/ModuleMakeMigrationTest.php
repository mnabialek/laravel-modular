<?php

namespace Tests\Console\Commands;

use Carbon\Carbon;
use Mnabialek\LaravelModular\Console\Commands\ModuleMakeMigration;
use Mnabialek\LaravelModular\Models\Module;
use Mnabialek\LaravelModular\Services\Config;
use Tests\Helpers\Application;
use Tests\UnitTestCase;
use Mockery as m;

class ModuleMakeMigrationTest extends UnitTestCase
{
    /** @test */
    public function it_displays_error_when_type_without_table()
    {
        $command = m::mock(ModuleMakeMigration::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $command->shouldReceive('verifyConfigExistence')->once()
            ->andReturn(null);

        $command->shouldReceive('proceed')->once()->withNoArgs()->passthru();

        $command->shouldReceive('argument')->once()->with('module');
        $command->shouldReceive('argument')->once()->with('name')->once();
        $command->shouldReceive('option')->once()->with('type')->once()
            ->andReturn('type value');
        $command->shouldReceive('option')->once()->with('table')->once();

        $command->shouldReceive('error')->once()
            ->with('You need to use both options --type and --table when using any of them');

        $command->shouldNotReceive('verifyExisting');

        $command->handle();
    }

    /** @test */
    public function it_displays_error_when_table_without_type()
    {
        $command = m::mock(ModuleMakeMigration::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $command->shouldReceive('verifyConfigExistence')->once()
            ->andReturn(null);

        $command->shouldReceive('proceed')->once()->withNoArgs()->passthru();

        $command->shouldReceive('argument')->once()->with('module');
        $command->shouldReceive('argument')->once()->with('name')->once();
        $command->shouldReceive('option')->once()->with('type')->once();
        $command->shouldReceive('option')->once()->with('table')->once()
            ->andReturn('table value');

        $command->shouldReceive('error')->once()
            ->with('You need to use both options --type and --table when using any of them');

        $command->shouldNotReceive('verifyExisting');

        $command->handle();
    }

    /** @test */
    public function it_displays_error_when_no_stub_file_in_config()
    {
        $app = m::mock(Application::class);

        $command = m::mock(ModuleMakeMigration::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $command->setLaravel($app);

        $command->shouldReceive('verifyConfigExistence')->once()
            ->andReturn(null);

        $command->shouldReceive('proceed')->once()->withNoArgs()->passthru();

        $command->shouldReceive('argument')->once()->with('module')
            ->andReturn('A');
        $command->shouldReceive('argument')->once()->with('name')->once()
            ->andReturn('sample name');
        $command->shouldReceive('option')->once()->with('type')->once();
        $command->shouldReceive('option')->once()->with('table')->once();

        $moduleAMock = m::mock(Module::class);

        $modules = collect([$moduleAMock]);

        $command->shouldReceive('verifyExisting')->once()
            ->with(m::on(function ($arg) use ($moduleAMock) {
                return $arg->first() == 'A';
            }))->andReturn($modules);

        $command->shouldReceive('createMigrationFile')->once()
            ->with($moduleAMock, 'sample name', null, null)->passthru();

        $command->shouldReceive('getStubGroup')->once()
            ->andReturn('sample stub group');

        $config = m::mock(Config::class);

        $app->shouldReceive('offsetGet')->times(2)->with('modular.config')
            ->andReturn($config);
        $config->shouldReceive('migrationDefaultType')->once()
            ->andReturn('sample type');
        $config->shouldReceive('migrationStubFileName')->once()
            ->with('sample type')->andReturn(null);

        $command->shouldReceive('error')->once()
            ->with('There is no sample type in module_migrations.types registered in configuration file');

        $command->handle();
    }

    /** @test */
    public function it_generates_migration_when_no_errors()
    {
        $app = m::mock(Application::class);

        $command = m::mock(ModuleMakeMigration::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $command->setLaravel($app);

        $command->shouldReceive('verifyConfigExistence')->once()
            ->andReturn(null);

        $tableName = 'sample table';
        $userMigrationName = 'sample name';
        $migrationType = 'sample type';
        $moduleName = 'ModuleA';

        $command->shouldReceive('proceed')->once()->withNoArgs()->passthru();

        $command->shouldReceive('argument')->once()->with('module')
            ->andReturn($moduleName);
        $command->shouldReceive('argument')->once()->with('name')->once()
            ->andReturn($userMigrationName);
        $command->shouldReceive('option')->once()->with('type')->once()
            ->andReturn($migrationType);
        $command->shouldReceive('option')->once()->with('table')->once()
            ->andReturn($tableName);

        $moduleAMock = m::mock(Module::class);
        $moduleAMock->shouldReceive('getName')->times(2)->withNoArgs()
            ->andReturn($moduleName);

        $modules = collect([$moduleAMock]);

        $command->shouldReceive('verifyExisting')->once()
            ->with(m::on(function ($arg) use ($moduleAMock, $moduleName) {
                return $arg->first() == $moduleName;
            }))->andReturn($modules);

        $command->shouldReceive('createMigrationFile')->once()
            ->with(m::on(function ($arg) use ($moduleName) {
                return $arg instanceof Module && $arg->getName() == $moduleName;
            }), $userMigrationName, $migrationType, $tableName)->passthru();

        $stubGroupName = 'sample stub group';
        $migrationStubFileName = 'sample stub file';
        $modulePath = 'sample A path';

        $command->shouldReceive('getStubGroup')->once()
            ->andReturn($stubGroupName);

        $config = m::mock(Config::class);

        $app->shouldReceive('offsetGet')->once()->with('modular.config')
            ->andReturn($config);
        $config->shouldNotReceive('migrationDefaultType');
        $config->shouldReceive('migrationStubFileName')->once()
            ->with($migrationType)->andReturn($migrationStubFileName);

        $now = Carbon::now();

        $expectedMigrationFileNames = [
            $now->format('Y_m_d_His') . '_' . snake_case($userMigrationName) .
            '.php',
            (clone $now)->subSecond(1)->format('Y_m_d_His') . '_' .
            snake_case($userMigrationName) . '.php',
            (clone $now)->addSecond(1)->format('Y_m_d_His') . '_' .
            snake_case($userMigrationName) . '.php',
        ];

        $fullMigrationName = m::anyOf(
            $modulePath . DIRECTORY_SEPARATOR .
            $expectedMigrationFileNames[0],
            $modulePath . DIRECTORY_SEPARATOR .
            $expectedMigrationFileNames[1],
            $modulePath . DIRECTORY_SEPARATOR .
            $expectedMigrationFileNames[2]
        );
        $command->shouldReceive('getMigrationFileName')->once()
            ->with($userMigrationName)->passthru();

        $migrationClass = studly_case($userMigrationName);

        $moduleAMock->shouldReceive('getMigrationsPath')->once()->withNoArgs()
            ->andReturn($modulePath);

        $command->shouldReceive('copyStubFileIntoModule')->once()
            ->with($moduleAMock, $migrationStubFileName, $stubGroupName,
                $fullMigrationName,
                ['migrationClass' => $migrationClass, 'table' => $tableName]);

        $expectedInfo = m::anyOf(
            '[Module ' . $moduleName . '] Created migration file: ' .
            $expectedMigrationFileNames[0],
            '[Module ' . $moduleName . '] Created migration file: ' .
            $expectedMigrationFileNames[1],
            '[Module ' . $moduleName . '] Created migration file: ' .
            $expectedMigrationFileNames[2]
        );

        $command->shouldReceive('info')->once()
            ->with($expectedInfo);

        $command->handle();
    }
}
