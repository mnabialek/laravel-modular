<?php

namespace Tests\Console\Commands;

use Mnabialek\LaravelModular\Console\Commands\ModuleMake;
use Mnabialek\LaravelModular\Models\Module;
use Mnabialek\LaravelModular\Services\Config;
use Mnabialek\LaravelModular\Services\Modular;
use stdClass;
use Tests\Helpers\Application;
use Tests\UnitTestCase;
use Mockery as m;

class ModuleMakeTest extends UnitTestCase
{

    protected $command;

    protected $app;

    protected $stubGroupName = 'sample stub group name';

    /** @test */
    public function it_displays_warning_when_module_already_exists()
    {
        $modules = ['A', 'A'];
        $this->arrange($modules);

        // now loop
        $modular = m::mock(Modular::class);
        $this->app->shouldReceive('offsetGet')->times(1)->with('modular')
            ->andReturn($modular);

        $moduleAName = 'module A';

        $moduleA = m::mock(stdClass::class);
        $moduleA->shouldReceive('getName')->times(2)->andReturn($moduleAName);

        $this->command->shouldReceive('createModuleObject')->with('A')->once()
            ->andReturn($moduleA);

        $modular->shouldReceive('exists')->once()->with($moduleAName)
            ->andReturn(true);
        $this->command->shouldReceive('warn')->once()
            ->with("[Module {$moduleAName}] Module already exists - ignoring");
        $this->command->shouldNotReceive('createModule');
        $this->command->handle();
    }

    /** @test */
    public function it_displays_warning_when_module_directory_exists()
    {
        $modules = ['A', 'A'];
        $this->arrange($modules);

        // now loop
        $modular = m::mock(Modular::class);
        $this->app->shouldReceive('offsetGet')->times(1)->with('modular')
            ->andReturn($modular);

        $moduleAName = 'module A';

        $moduleA = m::mock(stdClass::class);
        $moduleA->shouldReceive('getName')->times(2)->andReturn($moduleAName);
        $moduleA->shouldReceive('getDirectory')->once()
            ->andReturn('module A directory');

        $this->command->shouldReceive('createModuleObject')->with('A')->once()
            ->andReturn($moduleA);

        $modular->shouldReceive('exists')->once()->with($moduleAName)
            ->andReturn(false);

        $file = m::mock(stdClass::class);
        $this->app->shouldReceive('offsetGet')->once()->with('files')
            ->andReturn($file);
        $file->shouldReceive('exists')->once()->with('module A directory')
            ->andReturn(true);
        $this->command->shouldReceive('warn')->once()
            ->with("[Module {$moduleAName}] Module already exists - ignoring");
        $this->command->shouldNotReceive('createModule');
        $this->command->handle();
    }

    /** @test */
    public function it_loops_through_all_modules()
    {
        $modules = ['A', 'A', 'B'];
        $this->arrange($modules);

        // now loop
        $modular = m::mock(Modular::class);
        $this->app->shouldReceive('offsetGet')->times(2)->with('modular')
            ->andReturn($modular);

        $moduleAName = 'module A name';

        $moduleA = m::mock(stdClass::class);
        $moduleA->shouldReceive('getName')->times(2)->andReturn($moduleAName);

        $this->command->shouldReceive('createModuleObject')->with('A')->once()
            ->andReturn($moduleA);

        $modular->shouldReceive('exists')->once()->with($moduleAName)
            ->andReturn(true);

        $this->command->shouldReceive('warn')->once()
            ->with("[Module {$moduleAName}] Module already exists - ignoring");

        $moduleB = m::mock(stdClass::class);
        $moduleBName = 'module B name';

        $moduleB->shouldReceive('getName')->times(2)->andReturn($moduleBName);
        $moduleB->shouldReceive('getDirectory')->once()
            ->andReturn('module B directory');

        $this->command->shouldReceive('createModuleObject')->with('B')->once()
            ->andReturn($moduleB);

        $modular->shouldReceive('exists')->once()->with($moduleBName)
            ->andReturn(false);

        $file = m::mock(stdClass::class);
        $this->app->shouldReceive('offsetGet')->once()->with('files')
            ->andReturn($file);
        $file->shouldReceive('exists')->once()->with('module B directory')
            ->andReturn(true);

        $this->command->shouldReceive('warn')->once()
            ->with("[Module {$moduleBName}] Module already exists - ignoring");

        $this->command->shouldNotReceive('createModule');
        $this->command->handle();
    }

    /** @test */
    public function it_creates_module_without_adding_to_config()
    {
        $modules = ['A', 'A'];
        list($moduleAName,) =
            $this->verifySuccessModuleCreationWithoutConfig($modules, 3);

        $this->command->shouldReceive('info')->once()
            ->with("[Module {$moduleAName}] Module was generated");

        $config = m::mock(Config::class);

        $this->app->shouldReceive('offsetGet')->times(2)->with('modular.config')
            ->andReturn($config);

        $configFile = 'config name';
        $config->shouldReceive('getConfigFilePath')->once()->withNoArgs()
            ->andReturn($configFile);
        $config->shouldReceive('autoAdd')->once()->andReturn(false);

        $this->command->shouldReceive('info')->once()
            ->with("[Module {$moduleAName}] - auto-adding to config file turned off" .
                "\nPlease add this module manually into {$configFile} file if you want to use it");

        $this->command->handle();
    }

    /** @test */
    public function it_creates_module_with_adding_to_config_when_pattern_found()
    {
        $modules = ['A', 'A'];
        list($moduleAName, $moduleA) =
            $this->verifySuccessModuleCreationWithoutConfig($modules, 3);

        $this->command->shouldReceive('info')->once()
            ->with("[Module {$moduleAName}] Module was generated");

        $config = m::mock(Config::class);

        $this->app->shouldReceive('offsetGet')->times(4)->with('modular.config')
            ->andReturn($config);

        $configFile = 'config name';
        $config->shouldReceive('getConfigFilePath')->once()->withNoArgs()
            ->andReturn($configFile);
        $config->shouldReceive('autoAdd')->once()->andReturn(true);

        $config->shouldReceive('autoAddPattern')->once()
            ->andReturn('#(START)(.*)(END)#sm');

        $file = m::mock(stdClass::class);

        $autoAddTemplate = 'module => [xxx]';

        $this->app->shouldReceive('offsetGet')->times(2)->with('files')
            ->andReturn($file);
        $file->shouldReceive('get')->once()->with($configFile)
            ->andReturn('START abc, END');
        $config->shouldReceive('autoAddTemplate')->once()->withNoArgs()
            ->andReturn($autoAddTemplate);
        $this->command->shouldReceive('replace')->once()
            ->with($autoAddTemplate, $moduleA)->andReturn('module => [yyy]');
        $file->shouldReceive('put')->once()
            ->with($configFile, 'START abc, module => [yyy]END');

        $this->command->shouldReceive('comment')->once()
            ->with("[Module {$moduleAName}] Added into config file {$configFile}");

        $this->command->handle();
    }

    /** @test */
    public function it_creates_module_with_adding_to_config_when_no_pattern_found()
    {
        $modules = ['A', 'A'];
        list($moduleAName, $moduleA) =
            $this->verifySuccessModuleCreationWithoutConfig($modules, 4);

        $this->command->shouldReceive('info')->once()
            ->with("[Module {$moduleAName}] Module was generated");

        $config = m::mock(Config::class);

        $this->app->shouldReceive('offsetGet')->times(3)->with('modular.config')
            ->andReturn($config);

        $configFile = 'config name';
        $config->shouldReceive('getConfigFilePath')->once()->withNoArgs()
            ->andReturn($configFile);
        $config->shouldReceive('autoAdd')->once()->andReturn(true);

        $config->shouldReceive('autoAddPattern')->once()
            ->andReturn('#(START)(.*)(END)#sm');

        $file = m::mock(stdClass::class);

        $autoAddTemplate = 'module => [xxx]';

        $this->app->shouldReceive('offsetGet')->times(1)->with('files')
            ->andReturn($file);
        $file->shouldReceive('get')->once()->with($configFile)
            ->andReturn('X abc, Y');
        $config->shouldNotReceive('autoAddTemplate');
        $this->command->shouldNotReceive('replace');
        $file->shouldNotReceive('put');

        $this->command->shouldReceive('warn')->once()
            ->with("[Module {$moduleAName}] It was impossible to add module into {$configFile} file.\n Please make sure you haven't changed structure of this file. " .
                "At the moment add <info>{$moduleAName}</info> to this file manually");

        $this->command->handle();
    }

    protected function verifySuccessModuleCreationWithoutConfig(array $modules, $getNameTimes)
    {
        $this->arrange($modules);

        // now loop
        $modular = m::mock(Modular::class);
        $this->app->shouldReceive('offsetGet')->times(1)->with('modular')
            ->andReturn($modular);

        $moduleAName = 'Module A name';

        $moduleA = m::mock(Module::class);
        $moduleA->shouldReceive('getName')->times($getNameTimes)
            ->andReturn($moduleAName);
        $moduleA->shouldReceive('getDirectory')->once()
            ->andReturn('module A directory');

        $this->command->shouldReceive('createModuleObject')->with('A')->once()
            ->andReturn($moduleA);

        $modular->shouldReceive('exists')->once()->with($moduleAName)
            ->andReturn(false);

        $file = m::mock(stdClass::class);
        $this->app->shouldReceive('offsetGet')->once()->with('files')
            ->andReturn($file);
        $file->shouldReceive('exists')->once()->with('module A directory')
            ->andReturn(false);

        $this->command->shouldReceive('createModule')->once()
            ->with($moduleA, $this->stubGroupName)->passthru();

        $this->command->shouldReceive('createModuleDirectories')->once()
            ->with($moduleA, $this->stubGroupName);

        $this->command->shouldReceive('createModuleFiles')->once()
            ->with($moduleA, $this->stubGroupName);

        $this->command->shouldReceive('addModuleToConfigurationFile')->once()
            ->with($moduleA)->passthru();

        return [$moduleAName, $moduleA];
    }

    protected function arrange(array $modules)
    {
        $this->app = m::mock(Application::class);

        $this->command = m::mock(ModuleMake::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $this->command->setLaravel($this->app);
        $this->command->shouldReceive('verifyConfigExistence')->once()
            ->andReturn(null);

        $this->command->shouldReceive('proceed')->once()->withNoArgs()
            ->passthru();

        $this->command->shouldReceive('argument')->once()->with('module')
            ->andReturn($modules);

        $this->command->shouldReceive('getStubGroup')->once()
            ->andReturn($this->stubGroupName);

        $this->command->shouldReceive('verifyStubGroup')->once()
            ->with($this->stubGroupName);
    }
}
