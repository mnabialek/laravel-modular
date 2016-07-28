<?php

namespace Tests\Console\Traits;

use Exception;
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
}
