<?php

namespace Test\Services;

use Mnabialek\LaravelModular\Services\Config;
use stdClass;
use Tests\Helpers\Application;
use Tests\UnitTestCase;
use Mockery as m;

class ConfigTest extends UnitTestCase
{
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

    /** @test */
    public function it_returns_valid_modules()
    {
        $values = ['A', 'B', 'C'];
        $config = $this->arrange('modules', $values);
        $this->assertEquals($values, $config->modules());
    }

    /** @test */
    public function it_returns_valid_directory()
    {
        $value = 'some/dir';
        $config = $this->arrange('directory', $value);
        $this->assertEquals($value, $config->directory());
    }

    /** @test */
    public function it_returns_valid_namespace()
    {
        $value = 'Some\\Namespace\\';
        $config = $this->arrange('namespace', $value);
        $this->assertEquals($value, $config->modulesNamespace());
    }

    /** @test */
    public function it_returns_valid_seeder_namespace()
    {
        $value = 'Some\\Namespace\\';
        $config = $this->arrange('module_seeding.namespace', $value);
        $this->assertEquals($value, $config->seederNamespace());
    }

    /** @test */
    public function it_returns_valid_seeder_filename()
    {
        $value = '{class}A\B\C\\';
        $config = $this->arrange('module_seeding.file', $value);
        $this->assertEquals($value, $config->seederFile());
    }

    /** @test */
    public function it_returns_valid_start_separator()
    {
        $value = '{|';
        $config = $this->arrange('separators.start', $value);
        $this->assertEquals($value, $config->startSeparator());
    }

    /** @test */
    public function it_returns_valid_end_separator()
    {
        $value = '|}';
        $config = $this->arrange('separators.end', $value);
        $this->assertEquals($value, $config->endSeparator());
    }

    /** @test */
    public function it_returns_valid_module_stub_group()
    {
        $value = 'popular';
        $config = $this->arrange('stubs.module_default_group', $value);
        $this->assertEquals($value, $config->stubsDefaultGroup());
    }

    /** @test */
    public function it_returns_valid_files_stub_group()
    {
        $value = 'interesting';
        $config = $this->arrange('stubs.files_default_group', $value);
        $this->assertEquals($value, $config->filesStubsDefaultGroup());
    }

    /** @test */
    public function it_returns_valid_default_migration_type()
    {
        $value = 'foo';
        $config = $this->arrange('module_migrations.default_type', $value);
        $this->assertEquals($value, $config->migrationDefaultType());
    }

    /** @test */
    public function it_returns_valid_stub_name_for_given_type()
    {
        $value = 'bar';
        $type = 'foo';
        $config = $this->arrange('module_migrations.types.' . $type, $value);
        $this->assertEquals($value, $config->migrationStubFileName($type));
    }

    /** @test */
    public function it_returns_valid_path_of_stubs()
    {
        $value = 'foo/bar/path';
        $config = $this->arrange('stubs.path', $value);
        $this->assertEquals($value, $config->stubsPath());
    }

    /** @test */
    public function it_returns_valid_stub_directory_for_given_stub_group()
    {
        $value = 'bar';
        $group = 'foo';
        $config = $this->arrange("stubs_groups.{$group}.stub_directory", $value,
            $group);
        $this->assertEquals($value, $config->stubGroupDirectory($group));
    }

    /** @test */
    public function it_returns_valid_stub_group_directories()
    {
        $values = ['A', 'B', 'C'];
        $group = 'foo';
        $config =
            $this->arrange("stubs_groups.{$group}.directories", $values, []);
        $this->assertEquals($values, $config->stubGroupDirectories($group));
    }

    /** @test */
    public function it_returns_valid_stub_group_files()
    {
        $values = ['A/1.php', 'B/2.php', 'C/3.php'];
        $group = 'foo';
        $config = $this->arrange("stubs_groups.{$group}.files", $values, []);
        $this->assertEquals($values, $config->stubGroupFiles($group));
    }

    /** @test */
    public function it_returns_valid_stub_groups()
    {
        $values = ['foo' => 'bar', 'baz' => 'foo'];
        $config = $this->arrange('stubs_groups', $values, []);
        $this->assertEquals(['foo', 'baz'], $config->stubGroups());
    }

    /** @test */
    public function it_returns_valid_auto_add_status()
    {
        $value = true;
        $config = $this->arrange('module_make.auto_add', $value, false);
        $this->assertEquals($value, $config->autoAdd());
    }

    /** @test */
    public function it_returns_valid_auto_add_pattern()
    {
        $value = 'some/pattern';
        $config = $this->arrange('module_make.pattern', $value);
        $this->assertEquals($value, $config->autoAddPattern());
    }

    /** @test */
    public function it_returns_valid_auto_add_template()
    {
        $value = 'auto-add-template';
        $config = $this->arrange('module_make.module_template', $value);
        $this->assertEquals($value, $config->autoAddTemplate());
    }

    /** @test */
    public function it_returns_valid_service_provider_file()
    {
        $value = 'sample/service-provider/file.php';
        $config = $this->arrange('module_service_providers.file', $value);
        $this->assertEquals($value, $config->serviceProviderFile());
    }

    /** @test */
    public function it_returns_valid_migrations_path()
    {
        $value = 'sample/migrations/path';
        $config = $this->arrange('module_migrations.path', $value);
        $this->assertEquals($value, $config->migrationsPath());
    }

    /** @test */
    public function it_returns_valid_service_provider_namespace()
    {
        $value = 'ServiceProvider\\Namespace';
        $config = $this->arrange('module_service_providers.namespace', $value);
        $this->assertEquals($value, $config->serviceProviderNamespace());
    }

    /** @test */
    public function it_returns_valid_routing_controller_namespace()
    {
        $value = 'Routing\\Namespace';
        $config =
            $this->arrange('module_routing.route_group_namespace', $value);
        $this->assertEquals($value, $config->routingControllerNamespace());
    }

    /** @test */
    public function it_returns_valid_routing_file()
    {
        $value = 'routing/file.php';
        $config = $this->arrange('module_routing.file', $value);
        $this->assertEquals($value, $config->routingFile());
    }

    /** @test */
    public function it_returns_valid_factory_file()
    {
        $value = 'factory/file.php';
        $config = $this->arrange('module_factories.file', $value);
        $this->assertEquals($value, $config->factoryFile());
    }

    protected function arrange($configKey, $returnValue, $defaultConfigValue = null)
    {
        $appConfig = m::mock(stdClass::class);
        $app = m::mock(Application::class);
        $app->shouldReceive('offsetGet')->once()->with('config')
            ->andReturn($appConfig);

        $appConfig->shouldReceive('get')->once()->with('modular.' . $configKey,
            $defaultConfigValue)->andReturn($returnValue);
        $config = m::mock(Config::class, [$app])->makePartial();
        $config->shouldReceive('configName')->once()->withNoArgs()->passthru();

        return $config;
    }
}
