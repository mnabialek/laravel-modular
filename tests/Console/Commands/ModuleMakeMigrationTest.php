<?php

namespace Tests\Console\Commands;

use Mnabialek\LaravelModular\Console\Commands\ModuleMakeMigration;

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
}
