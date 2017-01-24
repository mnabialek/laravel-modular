<?php

namespace Tests\Console\Commands;

use stdClass;
use Mockery as m;
use Tests\UnitTestCase;
use Mnabialek\LaravelModular\Console\Commands\ModuleSeed;

class ModuleSeedTest extends UnitTestCase
{
    /** @test */
    public function it_does_nothing_when_there_were_errors()
    {
        $command = m::mock(ModuleSeed::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $command->shouldReceive('verifyConfigExistence')->once()
            ->andReturn(null);

        $command->shouldReceive('proceed')->once()->withNoArgs()->passthru();

        $modules = ['A', 'B', 'C', 'A'];

        $command->shouldReceive('argument')->once()->with('module')
            ->andReturn($modules);

        $command->shouldReceive('verifyActive')->once()
            ->with(m::on(function ($arg) use ($modules) {
                return $arg->all() == ['A', 'B', 'C'];
            }))->andReturn(false);

        $command->handle();
    }

    /** @test */
    public function it_does_nothing_when_given_modules_are_not_active()
    {
        $command = m::mock(ModuleSeed::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $command->shouldReceive('verifyConfigExistence')->once()
            ->andReturn(null);

        $command->shouldReceive('proceed')->once()->withNoArgs()->passthru();

        $modules = ['A', 'B', 'C', 'A'];
        $command->shouldReceive('argument')->once()->with('module')
            ->andReturn($modules);

        $command->shouldReceive('verifyActive')->once()
            ->with(m::on(function ($arg) {
                return $arg->all() == ['A', 'B', 'C'];
            }))->andReturn(collect([]));

        $command->handle();
    }

    /** @test */
    public function it_runs_valid_seeders_when_active_modules_given()
    {
        $command = m::mock(ModuleSeed::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $command->shouldReceive('verifyConfigExistence')->once()
            ->andReturn(null);

        $command->shouldReceive('proceed')->once()->withNoArgs()->passthru();

        $modules = ['A', 'B', 'C', 'D'];

        $command->shouldReceive('argument')->once()->with('module')
            ->andReturn($modules);

        $moduleAMock = m::mock(stdClass::class);
        $moduleBMock = m::mock(stdClass::class);
        $moduleDMock = m::mock(stdClass::class);

        $activeModules = collect([$moduleAMock, $moduleBMock, $moduleDMock]);

        $command->shouldReceive('verifyActive')->once()
            ->with(m::on(function ($arg) use ($modules) {
                return $arg->all() == $modules;
            }))->andReturn($activeModules);

        $options = ['option1' => 'value1', 'option2' => 'value2'];

        $command->shouldReceive('getOptions')->once()->withNoArgs()
            ->andReturn($options);

        $command->shouldReceive('option')->times(3)->with('class')
            ->andReturn('sampleClass');

        $moduleAMock->shouldReceive('seederClass')->once()
            ->with('sampleClass')->andReturn('moduleAClass');

        $argOptions = $options;
        $argOptions['--class'] = 'moduleAClass';

        $command->shouldReceive('call')->once()->with('db:seed', $argOptions)
            ->andReturn(0);
        $moduleAMock->shouldReceive('name')->once()->andReturn('A');

        $command->shouldReceive('info')->once()
            ->with('[Module A] Seeded: moduleAClass');

        $moduleBMock->shouldReceive('seederClass')->once()
            ->with('sampleClass')->andReturn('moduleBClass');

        $argOptions = $options;
        $argOptions['--class'] = 'moduleBClass';

        $command->shouldReceive('call')->once()->with('db:seed', $argOptions)
            ->andReturn(2);
        $moduleBMock->shouldReceive('name')->once()->andReturn('B');

        $command->shouldReceive('error')->once()
            ->with('[Module B] There was a problem with running seeder moduleBClass');

        $moduleDMock->shouldReceive('seederClass')->once()
            ->with('sampleClass')->andReturn('moduleDClass');

        $argOptions = $options;
        $argOptions['--class'] = 'moduleDClass';

        $command->shouldReceive('call')->once()->with('db:seed', $argOptions)
            ->andReturn(0);
        $moduleDMock->shouldReceive('name')->once()->andReturn('D');

        $command->shouldReceive('info')->once()
            ->with('[Module D] Seeded: moduleDClass');

        $command->handle();
    }
}
