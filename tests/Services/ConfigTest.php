<?php

namespace Test\Services;

use Mnabialek\LaravelModular\Services\Config;
use Tests\Helpers\Application;
use Tests\UnitTestCase;
use Mockery as m;

class ConfigTest extends UnitTestCase
{
    public function setUp()
    {
        parent::setUp();

    }

    /** @test */
    public function it_returns_valid_config_file_name()
    {
        $config = m::mock(Config::class)->makePartial();
        $this->assertEquals('modular', $config->configName());
    }

    /** @test */
    public function it_returns_valid_config_file_path()
    {
        $app = m::mock(Application::class);
        $app->shouldReceive('offsetGet')->once()->with('config.path')
            ->andReturn('sample/path');
        $config = m::mock(Config::class, [$app])->makePartial();

        $this->assertEquals('sample/path' . DIRECTORY_SEPARATOR . 'modular.php',
            $config->configPath());
    }
}
