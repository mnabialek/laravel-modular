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
        $creator = m::mock(ModuleCreator::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $modularConfig = m::mock(Config::class);
        $stubGroup = 'foo';
        $creator->setLaravel($app);
        $stubDirectory = 'stub/directory';

        $app->shouldReceive('offsetGet')->once()->with('modular.config')
            ->andReturn($modularConfig);
        $modularConfig->shouldReceive('stubGroups')->once()->withNoArgs()
            ->andReturn(['foo', 'bar']);

        $creator->shouldReceive('getStubGroupDirectory')->once()
            ->with($stubGroup)->andReturn($stubDirectory);

        $creator->shouldReceive('exists')->once()->with($stubDirectory)
            ->andReturn(false);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Stub group directory {$stubDirectory} does not exist");

        $creator->runVerifyStubGroup($stubGroup);
    }

    /** @test */
    public function it_doesnt_throw_exception_when_given_stub_group_is_fine()
    {
        $app = m::mock(Application::class);
        $creator = m::mock(ModuleCreator::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $modularConfig = m::mock(Config::class);
        $stubGroup = 'foo';
        $creator->setLaravel($app);
        $stubDirectory = 'stub/directory';

        $app->shouldReceive('offsetGet')->once()->with('modular.config')
            ->andReturn($modularConfig);
        $modularConfig->shouldReceive('stubGroups')->once()->withNoArgs()
            ->andReturn(['foo', 'bar']);

        $creator->shouldReceive('getStubGroupDirectory')->once()
            ->with($stubGroup)->andReturn($stubDirectory);

        $creator->shouldReceive('exists')->once()->with($stubDirectory)
            ->andReturn(true);

        $result = $creator->runVerifyStubGroup($stubGroup);
        $this->assertEquals(null, $result);
    }

    /** @test */
    public function it_throws_exception_when_config_file_does_not_exist()
    {
        $app = m::mock(Application::class);
        $creator = m::mock(ModuleCreator::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $modularConfig = m::mock(Config::class);
        $creator->setLaravel($app);

        $app->shouldReceive('offsetGet')->times(1)->with('modular.config')
            ->andReturn($modularConfig);
        $modularConfig->shouldReceive('configFilePath')->once()
            ->andReturn('foo');

        $creator->shouldReceive('exists')->once()->with('foo')
            ->andReturn(false);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Config file does not exists. Please run php artisan vendor:publish (see docs for details)');

        $creator->runVerifyConfigExistence();
    }

    /** @test */
    public function it_doesnt_throw_exception_when_config_file_exist()
    {
        $app = m::mock(Application::class);
        $creator = m::mock(ModuleCreator::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $modularConfig = m::mock(Config::class);
        $creator->setLaravel($app);

        $app->shouldReceive('offsetGet')->times(1)->with('modular.config')
            ->andReturn($modularConfig);
        $modularConfig->shouldReceive('configFilePath')->once()
            ->andReturn('foo');

        $creator->shouldReceive('exists')->once()->with('foo')
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
        $moduleA->shouldReceive('name')->once()->andReturn($moduleName);

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
        $creator = m::mock(ModuleCreator::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $modularConfig = m::mock(Config::class);
        $creator->setLaravel($app);

        $stubGroup = 'stub group';

        $moduleA = m::mock(Module::class);
        $moduleA->shouldReceive('foo')->andReturn('bar');

        $app->shouldReceive('offsetGet')->times(1)->with('modular.config')
            ->andReturn($modularConfig);
        $modularConfig->shouldReceive('stubGroupDirectories')->once()
            ->with($stubGroup)->andReturn(['foo', 'bar', 'baz/foo', 'foo']);

        $moduleArgument = m::on(function ($arg) {
            return $arg instanceof Module && $arg->foo() == 'bar';
        });

        $creator->shouldReceive('createDirectory')->once()
            ->with($moduleArgument, 'foo');
        $creator->shouldReceive('createDirectory')->once()
            ->with($moduleArgument, 'bar');
        $creator->shouldReceive('createDirectory')->once()
            ->with($moduleArgument, 'baz/foo');

        $result = $creator->runCreateModuleDirectories($moduleA, $stubGroup);

        $this->assertEquals(null, $result);
    }

    /** @test */
    public function it_displays_warning_when_no_module_files_without_submodule()
    {
        $this->verifyWarningDisplayedForModule(null);
    }

    /** @test */
    public function it_displays_warning_when_no_module_files_with_submodule()
    {
        $this->verifyWarningDisplayedForModule('foo');
    }

    /** @test */
    public function it_creates_module_files_without_submodule()
    {
        $app = m::mock(Application::class);
        $creator = m::mock(ModuleCreator::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $modularConfig = m::mock(Config::class);
        $creator->setLaravel($app);

        $stubGroup = 'stub group';

        $moduleA = m::mock(Module::class);
        $moduleA->shouldReceive('foo')->andReturn('something');

        $app->shouldReceive('offsetGet')->times(1)->with('modular.config')
            ->andReturn($modularConfig);

        $map = [
            'foo' => 'foo.stub',
            'bar' => 'bar.stub',
        ];

        $modularConfig->shouldReceive('stubGroupFiles')->once()
            ->with($stubGroup)->andReturn($map);

        $moduleArgument = m::on(function ($arg) {
            return $arg instanceof Module && $arg->foo() == 'something';
        });

        $creator->shouldReceive('copyStubFileIntoModule')->once()
            ->with($moduleArgument, $map['foo'], $stubGroup, 'foo', []);
        $creator->shouldReceive('copyStubFileIntoModule')->once()
            ->with($moduleArgument, $map['bar'], $stubGroup, 'bar', []);

        $result = $creator->runCreateModuleFiles($moduleA, $stubGroup, null);

        $this->assertEquals(true, $result);
    }

    /** @test */
    public function it_creates_module_files_with_submodule()
    {
        $app = m::mock(Application::class);
        $creator = m::mock(ModuleCreator::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $modularConfig = m::mock(Config::class);
        $creator->setLaravel($app);

        $stubGroup = 'stub group';
        $submodule = 'main module';

        $moduleA = m::mock(Module::class);
        $moduleA->shouldReceive('foo')->andReturn('something');

        $app->shouldReceive('offsetGet')->times(1)->with('modular.config')
            ->andReturn($modularConfig);

        $map = [
            'foo' => 'foo.stub',
            'bar' => 'bar.stub',
        ];

        $modularConfig->shouldReceive('stubGroupFiles')->once()
            ->with($stubGroup)->andReturn($map);

        $moduleArgument = m::on(function ($arg) {
            return $arg instanceof Module && $arg->foo() == 'something';
        });

        $creator->shouldReceive('copyStubFileIntoModule')->once()
            ->with($moduleArgument, $map['foo'], $stubGroup, 'foo',
                ['class' => $submodule]);
        $creator->shouldReceive('copyStubFileIntoModule')->once()
            ->with($moduleArgument, $map['bar'], $stubGroup, 'bar',
                ['class' => $submodule]);

        $result =
            $creator->runCreateModuleFiles($moduleA, $stubGroup, $submodule);

        $this->assertEquals(true, $result);
    }

    /** @test */
    public function it_gets_error_when_stub_path_does_not_exist_when_copy_stub_file()
    {
        $creator = m::mock(ModuleCreator::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $stubGroup = 'stub group';
        $stubPath = 'stub/path';
        $stubFile = 'some-stub-file';
        $replacements = ['foo' => 'bar', 'baz' => 'foo'];
        $moduleFile = 'module-file.stub';
        $fullStubPath = $stubPath . DIRECTORY_SEPARATOR . $stubFile;

        $moduleA = m::mock(Module::class);

        $creator->shouldReceive('getStubGroupDirectory')->once()
            ->with($stubGroup)->andReturn($stubPath);
        $creator->shouldReceive('exists')->once()
            ->with($fullStubPath)->andReturn(false);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Stub file {$fullStubPath} does NOT exist");

        $creator->runCopyStubFileIntoModule($moduleA, $stubFile,
            $stubGroup, $moduleFile, $replacements);
    }

    /** @test */
    public function it_gets_error_when_module_file_already_exists_when_copy_stub_file()
    {
        $creator = m::mock(ModuleCreator::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $stubGroup = 'stub group';
        $stubPath = 'stub/path';
        $stubFile = 'some-stub-file';
        $replacements = ['foo' => 'bar', 'baz' => 'foo'];
        $moduleFile = 'module-file{foo}.stub{baz}';
        $finalModuleFile = 'module-file-after.change';
        $fullStubPath = $stubPath . DIRECTORY_SEPARATOR . $stubFile;
        $moduleName = 'module name';

        $moduleA = m::mock(Module::class);
        $moduleA->shouldReceive('foo')->andReturn('bar');
        $moduleA->shouldReceive('name')->once()->withNoArgs()
            ->andReturn($moduleName);

        $creator->shouldReceive('getStubGroupDirectory')->once()
            ->with($stubGroup)->andReturn($stubPath);
        $creator->shouldReceive('exists')->once()
            ->with($fullStubPath)->andReturn(true);

        $creator->shouldReceive('replace')->once()
            ->with($moduleFile, m::on(function ($arg) {
                return $arg instanceof Module && $arg->foo() == 'bar';
            }), $replacements)->andReturn($finalModuleFile);

        $creator->shouldReceive('exists')->once()
            ->with($finalModuleFile)->andReturn(true);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("[Module {$moduleName}] File {$finalModuleFile} already exists");

        $creator->runCopyStubFileIntoModule($moduleA, $stubFile,
            $stubGroup, $moduleFile, $replacements);
    }

    /** @test */
    public function it_calls_valid_methods_when_copy_stub_file()
    {
        $creator = m::mock(ModuleCreator::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $stubGroup = 'stub group';
        $stubPath = 'stub/path';
        $stubFile = 'some-stub-file';
        $replacements = ['foo' => 'bar', 'baz' => 'foo'];
        $moduleFile = 'module-file{foo}.stub{baz}';
        $finalModuleFile = 'module-file-after.change';
        $fullStubPath = $stubPath . DIRECTORY_SEPARATOR . $stubFile;

        $moduleA = m::mock(Module::class);
        $moduleA->shouldReceive('foo')->andReturn('bar');

        $creator->shouldReceive('getStubGroupDirectory')->once()
            ->with($stubGroup)->andReturn($stubPath);
        $creator->shouldReceive('exists')->once()
            ->with($fullStubPath)->andReturn(true);

        $moduleArgument = m::on(function ($arg) {
            return $arg instanceof Module && $arg->foo() == 'bar';
        });

        $creator->shouldReceive('replace')->once()
            ->with($moduleFile, $moduleArgument, $replacements)
            ->andReturn($finalModuleFile);

        $creator->shouldReceive('exists')->once()
            ->with($finalModuleFile)->andReturn(false);

        $creator->shouldReceive('createMissingDirectory')->once()
            ->with($moduleArgument, $finalModuleFile);

        $creator->shouldReceive('createFile')->once()
            ->with($moduleArgument, $fullStubPath, $finalModuleFile,
                $replacements);

        $creator->runCopyStubFileIntoModule($moduleA, $stubFile,
            $stubGroup, $moduleFile, $replacements);
    }

    /** @test */
    public function it_doesnt_create_missing_directory_when_it_exists()
    {
        $creator = m::mock(ModuleCreator::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $file = 'foo/bar/baz.txt';

        $moduleA = m::mock(Module::class);

        $creator->shouldReceive('exists')->once()
            ->with('foo/bar')->andReturn(true);

        $creator->shouldNotReceive('createDirectory');
        $creator->runCreateMissingDirectory($moduleA, $file);
    }

    /** @test */
    public function it_creates_missing_directory_when_it_doesnt_exist()
    {
        $creator = m::mock(ModuleCreator::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $file = 'foo/bar/baz.txt';

        $moduleA = m::mock(Module::class);
        $moduleA->shouldReceive('foo')->andReturn('bar');

        $creator->shouldReceive('exists')->once()
            ->with('foo/bar')->andReturn(false);

        $creator->shouldReceive('createDirectory')->once()
            ->with(m::on(function ($arg) {
                return $arg instanceof Module and $arg->foo() == 'bar';
            }), 'foo/bar');
        $creator->runCreateMissingDirectory($moduleA, $file);
    }

    /** @test */
    public function it_throws_exception_cannot_create_file()
    {
        $app = m::mock(Application::class);
        $creator = m::mock(ModuleCreator::class)->makePartial();
        $creator->setLaravel($app);
        $sourceFile = 'source-file.txt';
        $replacements = ['foo' => 'bar', 'baz' => 'foo'];
        $moduleName = 'module A';
        $sourceFileContent = 'sample file content';
        $replacedFileContent = 'replaced file content';
        $destinationFile = 'destination-file.txt';
        $moduleDirectory = 'this/is/module/dir';

        $moduleA = m::mock(Module::class);
        $moduleA->shouldReceive('foo')->andReturn('bar');
        $moduleA->shouldReceive('name')->once()->andReturn($moduleName);
        $moduleA->shouldReceive('directory')->once()
            ->andReturn($moduleDirectory);

        $file = m::mock(stdClass::class);

        $app->shouldReceive('offsetGet')->times(2)->with('files')
            ->andReturn($file);

        $file->shouldReceive('get')->once()
            ->with($sourceFile)->andReturn($sourceFileContent);

        $creator->shouldReceive('replace')->once()
            ->with($sourceFileContent, m::on(function ($arg) {
                return $arg instanceof Module && $arg->foo() == 'bar';
            }), $replacements)
            ->andReturn($replacedFileContent);

        $file->shouldReceive('put')->once()
            ->with($moduleDirectory . DIRECTORY_SEPARATOR . $destinationFile,
                $replacedFileContent)->andReturn(false);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("[Module {$moduleName}] Cannot create file {$destinationFile}");

        $creator->runCreateFile($moduleA, $sourceFile, $destinationFile,
            $replacements);
    }

    /** @test */
    public function it_display_info_when_created_file()
    {
        $app = m::mock(Application::class);
        $creator = m::mock(ModuleCreator::class)->makePartial();
        $creator->setLaravel($app);
        $sourceFile = 'source-file.txt';
        $replacements = ['foo' => 'bar', 'baz' => 'foo'];
        $moduleName = 'module A';
        $sourceFileContent = 'sample file content';
        $replacedFileContent = 'replaced file content';
        $destinationFile = 'destination-file.txt';
        $moduleDirectory = 'this/is/module/dir';

        $moduleA = m::mock(Module::class);
        $moduleA->shouldReceive('foo')->andReturn('bar');
        $moduleA->shouldReceive('name')->once()->andReturn($moduleName);
        $moduleA->shouldReceive('directory')->once()
            ->andReturn($moduleDirectory);

        $file = m::mock(stdClass::class);

        $app->shouldReceive('offsetGet')->times(2)->with('files')
            ->andReturn($file);

        $file->shouldReceive('get')->once()
            ->with($sourceFile)->andReturn($sourceFileContent);

        $creator->shouldReceive('replace')->once()
            ->with($sourceFileContent, m::on(function ($arg) {
                return $arg instanceof Module && $arg->foo() == 'bar';
            }), $replacements)
            ->andReturn($replacedFileContent);

        $file->shouldReceive('put')->once()
            ->with($moduleDirectory . DIRECTORY_SEPARATOR . $destinationFile,
                $replacedFileContent)->andReturn(true);

        $creator->shouldReceive('line')->once()
            ->with("[Module {$moduleName}] Created file {$destinationFile}");

        $result =
            $creator->runCreateFile($moduleA, $sourceFile, $destinationFile,
                $replacements);

        $this->assertSame(null, $result);
    }

    /** @test */
    public function it_gets_stub_group_directory()
    {
        $app = m::mock(Application::class);
        $creator = m::mock(ModuleCreator::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $modularConfig = m::mock(Config::class);
        $creator->setLaravel($app);
        $stubGroup = 'stub group';

        $app->shouldReceive('offsetGet')->times(2)->with('modular.config')
            ->andReturn($modularConfig);

        $modularConfig->shouldReceive('stubsPath')->once()->withNoArgs()
            ->andReturn('stubs/path');

        $modularConfig->shouldReceive('stubGroupDirectory')->once()
            ->with($stubGroup)
            ->andReturn('group-sample-path');

        $creator->shouldReceive('normalizePath')->with('stubs/path' .
            DIRECTORY_SEPARATOR . 'group-sample-path')
            ->andReturn('normalized-directory');

        $result = $creator->runGetStubGroupDirectory($stubGroup);

        $this->assertSame('normalized-directory', $result);
    }

    /** @test */
    public function it_returns_true_if_file_exists()
    {
        $app = m::mock(Application::class);
        $creator = m::mock(ModuleCreator::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $file = m::mock(stdClass::class);
        $creator->setLaravel($app);
        $path = 'sample-path';

        $app->shouldReceive('offsetGet')->once()->with('files')
            ->andReturn($file);

        $file->shouldReceive('exists')->once()->with($path)->andReturn(true);

        $result = $creator->runExists($path);

        $this->assertSame(true, $result);
    }

    /** @test */
    public function it_returns_false_if_file_doesnt_exist()
    {
        $app = m::mock(Application::class);
        $creator = m::mock(ModuleCreator::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $file = m::mock(stdClass::class);
        $creator->setLaravel($app);
        $path = 'sample-path';

        $app->shouldReceive('offsetGet')->once()->with('files')
            ->andReturn($file);

        $file->shouldReceive('exists')->once()->with($path)->andReturn(false);

        $result = $creator->runExists($path);

        $this->assertSame(false, $result);
    }

    /** @test */
    public function it_creates_directory_when_no_issues_found()
    {
        $app = m::mock(Application::class);
        $creator = m::mock(ModuleCreator::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $file = m::mock(stdClass::class);
        $creator->setLaravel($app);
        $path = 'sample-path';

        $moduleName = 'main module';

        $moduleA = m::mock(Module::class);
        $moduleA->shouldReceive('name')->once()->andReturn($moduleName);
        $moduleA->shouldReceive('directory')->once()
            ->andReturn('module/dir');

        $creator->shouldReceive('exists')->once()->with($path)
            ->andReturn(false);

        $app->shouldReceive('offsetGet')->once()->with('files')
            ->andReturn($file);
        $file->shouldReceive('makeDirectory')->once()->with('module/dir' .
            DIRECTORY_SEPARATOR . $path, 0755, true)->andReturn(true);

        $creator->shouldReceive('line')->once()
            ->with("[Module {$moduleName}] Created directory {$path}");

        $result = $creator->runCreateDirectory($moduleA, $path);

        $this->assertSame(null, $result);
    }

    /** @test */
    public function it_throws_exception_when_cannot_create_directory()
    {
        $app = m::mock(Application::class);
        $creator = m::mock(ModuleCreator::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $file = m::mock(stdClass::class);
        $creator->setLaravel($app);
        $path = 'sample-path';

        $moduleName = 'main module';

        $moduleA = m::mock(Module::class);
        $moduleA->shouldReceive('name')->once()->andReturn($moduleName);
        $moduleA->shouldReceive('directory')->once()->andReturn('module/dir');

        $creator->shouldReceive('exists')->once()->with($path)
            ->andReturn(false);

        $app->shouldReceive('offsetGet')->once()->with('files')
            ->andReturn($file);
        $file->shouldReceive('makeDirectory')->once()->with('module/dir' .
            DIRECTORY_SEPARATOR . $path, 0755, true)->andReturn(false);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("[Module {$moduleName}] Cannot create directory {$path}");

        $creator->runCreateDirectory($moduleA, $path);
    }

    /** @test */
    public function it_does_nothing_when_directory_already_exists()
    {
        $app = m::mock(Application::class);
        $creator = m::mock(ModuleCreator::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $creator->setLaravel($app);
        $path = 'sample-path';

        $moduleA = m::mock(Module::class);

        $creator->shouldReceive('exists')->once()->with($path)
            ->andReturn(true);

        $result = $creator->runCreateDirectory($moduleA, $path);

        $this->assertSame(null, $result);
    }

    protected function verifyWarningDisplayedForModule($subModule)
    {
        $app = m::mock(Application::class);
        $creator = m::mock(ModuleCreator::class)->makePartial();
        $modularConfig = m::mock(Config::class);
        $creator->setLaravel($app);

        $stubGroup = 'stub group';
        $moduleName = 'main module';

        $moduleA = m::mock(Module::class);
        $moduleA->shouldReceive('name')->once()->andReturn($moduleName);

        $app->shouldReceive('offsetGet')->times(1)->with('modular.config')
            ->andReturn($modularConfig);
        $modularConfig->shouldReceive('stubGroupFiles')->once()
            ->with($stubGroup)->andReturn([]);

        $creator->shouldReceive('warn')->once()
            ->with("[Module {$moduleName}] No files created");

        $result =
            $creator->runCreateModuleFiles($moduleA, $stubGroup, $subModule);

        $this->assertEquals(false, $result);
    }
}
