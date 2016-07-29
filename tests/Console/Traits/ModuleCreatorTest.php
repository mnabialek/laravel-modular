<?php

namespace Tests\Console\Traits;

use Exception;
use Mnabialek\LaravelModular\Models\Module;
use Mnabialek\LaravelModular\Services\Config;
use stdClass;
use Tests\Helpers\Application;
use Tests\Helpers\ModuleCreator;
use Mockery as m;
use Tests\UnitTestCase;

class ModuleCreatorTest extends UnitTestCase
{
    /** @test */
    public function it_throws_exception_when_given_stub_group_is_not_in_config()
    {
        $app = m::mock(Application::class);
        $creator = m::mock(ModuleCreator::class)->makePartial();
        $modularConfig = m::mock(Config::class);
        $stubGroup = 'baz';
        $creator->setLaravel($app);

        $app->shouldReceive('offsetGet')->times(1)->with('modular.config')
            ->andReturn($modularConfig);
        $modularConfig->shouldReceive('stubGroups')->once()->withNoArgs()
            ->andReturn(['foo', 'bar']);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Stub group {$stubGroup} does not exist. You need to add it to stubs_groups");

        $creator->runVerifyStubGroup($stubGroup);
    }

    /** @test */
    public function it_throws_exception_when_given_stub_group_directory_does_not_exist()
    {
        $app = m::mock(Application::class);
        $creator = m::mock(ModuleCreator::class)->makePartial();
        $modularConfig = m::mock(Config::class);
        $stubGroup = 'foo';
        $creator->setLaravel($app);

        $app->shouldReceive('offsetGet')->times(3)->with('modular.config')
            ->andReturn($modularConfig);
        $modularConfig->shouldReceive('stubGroups')->once()->withNoArgs()
            ->andReturn(['foo', 'bar']);

        $modularConfig->shouldReceive('stubsPath')->once()->withNoArgs()
            ->andReturn('stubs/path');

        $modularConfig->shouldReceive('stubGroupDirectory')->once()
            ->with($stubGroup)
            ->andReturn('group-sample-path');

        $file = m::mock(stdClass::class);

        $app->shouldReceive('offsetGet')->once()->with('files')
            ->andReturn($file);

        $expectedDir = 'stubs/path' . DIRECTORY_SEPARATOR . 'group-sample-path';

        $file->shouldReceive('exists')->once()->with($expectedDir)
            ->andReturn(false);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Stub group directory {$expectedDir} does not exist");

        $creator->runVerifyStubGroup($stubGroup);
    }

    /** @test */
    public function it_doesnt_throw_exception_when_given_stub_group_is_fine()
    {
        $app = m::mock(Application::class);
        $creator = m::mock(ModuleCreator::class)->makePartial();
        $modularConfig = m::mock(Config::class);
        $stubGroup = 'foo';
        $creator->setLaravel($app);

        $app->shouldReceive('offsetGet')->times(3)->with('modular.config')
            ->andReturn($modularConfig);
        $modularConfig->shouldReceive('stubGroups')->once()->withNoArgs()
            ->andReturn(['foo', 'bar']);

        $modularConfig->shouldReceive('stubsPath')->once()->withNoArgs()
            ->andReturn('stubs/path');

        $modularConfig->shouldReceive('stubGroupDirectory')->once()
            ->with($stubGroup)
            ->andReturn('group-sample-path');

        $file = m::mock(stdClass::class);

        $app->shouldReceive('offsetGet')->once()->with('files')
            ->andReturn($file);

        $expectedDir = 'stubs/path' . DIRECTORY_SEPARATOR . 'group-sample-path';

        $file->shouldReceive('exists')->once()->with($expectedDir)
            ->andReturn(true);

        $result = $creator->runVerifyStubGroup($stubGroup);
        $this->assertEquals(null, $result);
    }

    /** @test */
    public function it_throws_exception_when_config_file_does_not_exist()
    {
        $app = m::mock(Application::class);
        $creator = m::mock(ModuleCreator::class)->makePartial();
        $modularConfig = m::mock(Config::class);
        $creator->setLaravel($app);

        $app->shouldReceive('offsetGet')->times(1)->with('modular.config')
            ->andReturn($modularConfig);
        $modularConfig->shouldReceive('configFilePath')->once()
            ->andReturn('foo');

        $file = m::mock(stdClass::class);
        $app->shouldReceive('offsetGet')->once()->with('files')
            ->andReturn($file);
        $file->shouldReceive('exists')->once()->with('foo')
            ->andReturn(false);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Config file does not exists. Please run php artisan vendor:publish (see docs for details)');

        $creator->runVerifyConfigExistence();
    }

    /** @test */
    public function it_doesnt_throw_exception_when_config_file_exist()
    {
        $app = m::mock(Application::class);
        $creator = m::mock(ModuleCreator::class)->makePartial();
        $modularConfig = m::mock(Config::class);
        $creator->setLaravel($app);

        $app->shouldReceive('offsetGet')->times(1)->with('modular.config')
            ->andReturn($modularConfig);
        $modularConfig->shouldReceive('configFilePath')->once()
            ->andReturn('foo');

        $file = m::mock(stdClass::class);
        $app->shouldReceive('offsetGet')->once()->with('files')
            ->andReturn($file);
        $file->shouldReceive('exists')->once()->with('foo')
            ->andReturn(true);

        $result = $creator->runVerifyConfigExistence();
        $this->assertEquals(null, $result);
    }

    /** @test */
    public function it_return_input_option_when_given_for_stub_group()
    {
        $app = m::mock(Application::class);
        $creator = m::mock(ModuleCreator::class)->makePartial();
        $creator->setLaravel($app);

        $creator->shouldReceive('option')->once()->with('group')
            ->andReturn('foo');

        $result = $creator->runGetStubGroup();
        $this->assertEquals('foo', $result);
    }

    /** @test */
    public function it_return_default_when_no_input_option_given_for_stub_group()
    {
        $app = m::mock(Application::class);
        $creator = m::mock(ModuleCreator::class)->makePartial();
        $modularConfig = m::mock(Config::class);
        $creator->setLaravel($app);

        $creator->shouldReceive('option')->once()->with('group')
            ->andReturn(null);

        $app->shouldReceive('offsetGet')->times(1)->with('modular.config')
            ->andReturn($modularConfig);
        $modularConfig->shouldReceive('stubsDefaultGroup')->once()
            ->andReturn('bar');

        $result = $creator->runGetStubGroup();
        $this->assertEquals('bar', $result);
    }

    /** @test */
    public function it_return_input_option_when_given_for_files_stub_group()
    {
        $app = m::mock(Application::class);
        $creator = m::mock(ModuleCreator::class)->makePartial();
        $creator->setLaravel($app);

        $creator->shouldReceive('option')->once()->with('group')
            ->andReturn('foo');

        $result = $creator->runGetFilesStubGroup();
        $this->assertEquals('foo', $result);
    }

    /** @test */
    public function it_return_default_when_no_input_option_given_for_files_stub_group()
    {
        $app = m::mock(Application::class);
        $creator = m::mock(ModuleCreator::class)->makePartial();
        $modularConfig = m::mock(Config::class);
        $creator->setLaravel($app);

        $creator->shouldReceive('option')->once()->with('group')
            ->andReturn(null);

        $app->shouldReceive('offsetGet')->times(1)->with('modular.config')
            ->andReturn($modularConfig);
        $modularConfig->shouldReceive('filesStubsDefaultGroup')->once()
            ->andReturn('bar');

        $result = $creator->runGetFilesStubGroup();
        $this->assertEquals('bar', $result);
    }

    /** @test */
    public function it_displays_warning_when_selected_module_stub_group_has_no_directories()
    {
        $app = m::mock(Application::class);
        $creator = m::mock(ModuleCreator::class)->makePartial();
        $modularConfig = m::mock(Config::class);
        $creator->setLaravel($app);

        $stubGroup = 'stub group';
        $moduleName = 'main module';

        $moduleA = m::mock(Module::class);
        $moduleA->shouldReceive('getName')->once()->andReturn($moduleName);

        $app->shouldReceive('offsetGet')->times(1)->with('modular.config')
            ->andReturn($modularConfig);
        $modularConfig->shouldReceive('stubGroupDirectories')->once()
            ->with($stubGroup)->andReturn([]);

        $creator->shouldReceive('warn')->once()
            ->with("[Module {$moduleName}] No explicit directories created");

        $result = $creator->runCreateModuleDirectories($moduleA, $stubGroup);
        $this->assertEquals(null, $result);
    }

    /** @test */
    public function it_creates_directories_when_selected_module_stub_group_has_directories()
    {
        $app = m::mock(Application::class);
        $creator = m::mock(ModuleCreator::class)->makePartial();
        $modularConfig = m::mock(Config::class);
        $creator->setLaravel($app);

        $stubGroup = 'stub group';
        $moduleName = 'main module';

        $moduleA = m::mock(Module::class);
        $moduleA->shouldReceive('getName')->times(2)->andReturn($moduleName);
        $moduleA->shouldReceive('getDirectory')->times(2)
            ->andReturn('module/dir');

        $app->shouldReceive('offsetGet')->times(1)->with('modular.config')
            ->andReturn($modularConfig);
        $modularConfig->shouldReceive('stubGroupDirectories')->once()
            ->with($stubGroup)->andReturn(['foo', 'bar', 'baz/foo', 'foo']);

        $file = m::mock(stdClass::class);
        $app->shouldReceive('offsetGet')->times(5)->with('files')
            ->andReturn($file);
        $file->shouldReceive('exists')->once()->with('foo')
            ->andReturn(false);
        $file->shouldReceive('makeDirectory')->once()->with('module/dir' .
            DIRECTORY_SEPARATOR . 'foo', 0755, true)->andReturn(true);
        $creator->shouldReceive('line')->once()
            ->with("[Module {$moduleName}] Created directory foo");

        $file->shouldReceive('exists')->once()->with('bar')
            ->andReturn(true);
        $file->shouldReceive('exists')->once()->with('baz/foo')
            ->andReturn(false);
        $file->shouldReceive('makeDirectory')->once()->with('module/dir' .
            DIRECTORY_SEPARATOR . 'baz/foo', 0755, true)->andReturn(false);
        $creator->shouldReceive('warn')->once()
            ->with("[Module {$moduleName}] Cannot create directory baz/foo");

        $result = $creator->runCreateModuleDirectories($moduleA, $stubGroup);
        $this->assertEquals(null, $result);
    }
}
