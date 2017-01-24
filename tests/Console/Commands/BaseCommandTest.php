<?php

namespace Tests\Console\Commands;

use Exception;
use Mockery as m;
use Tests\UnitTestCase;
use Illuminate\Foundation\Application;
use Mnabialek\LaravelModular\Console\Commands\BaseCommand;

class BaseCommandTest extends UnitTestCase
{
    /** @test */
    public function it_displays_error_when_no_config_exists()
    {
        $app = m::mock(Application::class)->makePartial();
        $command = m::mock(DummyCommand::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $command->setLaravel($app);

        $exceptionMessage = 'No config file';

        $command->shouldReceive('verifyConfigExistence')->once()
            ->andThrow(Exception::class, $exceptionMessage);
        $command->shouldReceive('error')->once()
            ->with($exceptionMessage);

        $command->shouldNotReceive('proceed');

        $command->handle();
    }

    /** @test */
    public function it_runs_proceed_method_when_config_exists()
    {
        $app = m::mock(Application::class)->makePartial();
        $command = m::mock(DummyCommand::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $command->setLaravel($app);

        $command->shouldReceive('verifyConfigExistence')->once()
            ->andReturn(null);
        $command->shouldNotReceive('error');

        $command->shouldReceive('proceed')->once()->withNoArgs();

        $command->handle();
    }
}

class DummyCommand extends BaseCommand
{
    protected function proceed()
    {
    }
}
