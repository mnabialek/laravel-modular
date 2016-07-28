<?php

namespace Tests\Console\Commands;

use Mnabialek\LaravelModular\Console\Commands\ModuleFiles;
use Mnabialek\LaravelModular\Models\Module;
use Mnabialek\LaravelModular\Services\Modular;
use Tests\Helpers\ApplicationClass;
use Tests\UnitTestCase;
use Mockery as m;

class ModuleFilesTest extends UnitTestCase
{
    protected $command;

    protected $app;

    protected $stubGroupName = 'sample stub group name';

    /** @test */
    public function it_displays_error_when_module_does_not_exist()
    {
        $moduleName = 'A';
        $subModules = ['B', 'C'];
        $this->arrange($moduleName, $subModules);

        $modular = m::mock(Modular::class);
        $this->app->shouldReceive('offsetGet')->times(1)->with('modular')
            ->andReturn($modular);

        $modular->shouldReceive('find')->once()->with($moduleName)
            ->andReturn(false);

        $this->command->shouldReceive('error')->once()
            ->with("[Module {$moduleName}] This module does not exist. " .
                "Run <comment>module:make {$moduleName}</comment> command first to create it");
        $this->command->handle();
    }

    /** @test */
    public function it_displays_error_when_cannot_generate_module_files()
    {
        $moduleName = 'A';
        $subModules = ['B'];
        $this->arrange($moduleName, $subModules);

        $modular = m::mock(Modular::class);
        $this->app->shouldReceive('offsetGet')->times(1)->with('modular')
            ->andReturn($modular);

        $moduleA = m::mock(Module::class);
        $moduleA->shouldReceive('foo')->andReturn('bar');
        $moduleA->shouldReceive('getName')->once()->andReturn('A');

        $modular->shouldReceive('find')->once()->with($moduleName)
            ->andReturn($moduleA);

        $moduleArgument = m::on(function ($arg) {
            return $arg instanceof Module && $arg->foo() == 'bar';
        });

        $this->command->shouldReceive('createSubModule')->once()
            ->with($moduleArgument, $subModules[0], $this->stubGroupName)
            ->passthru();

        $this->command->shouldReceive('createModuleDirectories')->once()
            ->with($moduleArgument, $this->stubGroupName);

        $this->command->shouldReceive('createModuleFiles')->once()
            ->with($moduleArgument, $subModules[0], $this->stubGroupName)
            ->andReturn(false);

        $this->command->shouldReceive('warn')->once()
            ->with("[Module {$moduleName}] Submodule {$subModules[0]} NOT created (all files already exist).");
        $this->command->handle();
    }

    /** @test */
    public function it_displays_info_when_can_generate_module_files()
    {
        $moduleName = 'A';
        $subModules = ['B'];
        $this->arrange($moduleName, $subModules);

        $modular = m::mock(Modular::class);
        $this->app->shouldReceive('offsetGet')->times(1)->with('modular')
            ->andReturn($modular);

        $moduleA = m::mock(Module::class);
        $moduleA->shouldReceive('foo')->andReturn('bar');
        $moduleA->shouldReceive('getName')->times(2)->andReturn('A');

        $modular->shouldReceive('find')->once()->with($moduleName)
            ->andReturn($moduleA);

        $moduleArgument = m::on(function ($arg) {
            return $arg instanceof Module && $arg->foo() == 'bar';
        });

        $this->command->shouldReceive('createSubModule')->once()
            ->with($moduleArgument, $subModules[0], $this->stubGroupName)
            ->passthru();

        $this->command->shouldReceive('createModuleDirectories')->once()
            ->with($moduleArgument, $this->stubGroupName);

        $this->command->shouldReceive('createModuleFiles')->once()
            ->with($moduleArgument, $subModules[0], $this->stubGroupName)
            ->andReturn(true);

        $this->command->shouldReceive('info')->once()
            ->with("[Module {$moduleName}] Submodule {$subModules[0]} was created.");

        $this->command->shouldReceive('comment')->once()
            ->with("You should register submodule routes (if any) into routes file for module {$moduleName}");
        $this->command->handle();
    }

    /** @test */
    public function it_runs_actions_for_multiple_submodules()
    {
        $moduleName = 'A';
        $subModules = ['B', 'C', 'B'];
        $this->arrange($moduleName, $subModules);

        $modular = m::mock(Modular::class);
        $this->app->shouldReceive('offsetGet')->times(1)->with('modular')
            ->andReturn($modular);

        $moduleA = m::mock(Module::class);
        $moduleA->shouldReceive('foo')->andReturn('bar');
        $modular->shouldReceive('find')->once()->with($moduleName)
            ->andReturn($moduleA);

        $moduleArgumentA = m::on(function ($arg) {
            return $arg instanceof Module && $arg->foo() == 'bar';
        });

        $this->command->shouldReceive('createSubModule')->once()
            ->with($moduleArgumentA, $subModules[0], $this->stubGroupName);
        $this->command->shouldReceive('createSubModule')->once()
            ->with($moduleArgumentA, $subModules[1], $this->stubGroupName);
        
        $this->command->handle();
    }

    protected function arrange($module, array $subModules)
    {
        $this->app = m::mock(ApplicationClass::class);

        $this->command = m::mock(ModuleFiles::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $this->command->setLaravel($this->app);
        $this->command->shouldReceive('verifyConfigExistence')->once()
            ->andReturn(null);

        $this->command->shouldReceive('proceed')->once()->withNoArgs()
            ->passthru();

        $this->command->shouldReceive('argument')->once()->with('module')
            ->andReturn($module);

        $this->command->shouldReceive('argument')->once()->with('name')
            ->andReturn($subModules);

        $this->command->shouldReceive('getFilesStubGroup')->once()
            ->andReturn($this->stubGroupName);

        $this->command->shouldReceive('verifyStubGroup')->once()
            ->with($this->stubGroupName);

    }
}
