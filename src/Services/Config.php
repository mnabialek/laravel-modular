<?php

namespace Mnabialek\LaravelModular\Services;

use Illuminate\Contracts\Foundation\Application;

class Config
{
    /**
     * Name for module config file (without .php extension)
     *
     * @var string
     */
    protected $configName = 'modular';

    /**
     * @var Application
     */
    protected $app;

    /**
     * Config constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Get config file name
     *
     * @return string
     */
    public function configName()
    {
        return $this->configName;
    }

    /**
     * Get full path where configuration file should be placed
     *
     * @return string
     */
    public function configPath()
    {
        return $this->app['config.path'] . DIRECTORY_SEPARATOR .
        "{$this->configName()}.php";
    }

    /**
     * Get value from module configuration file
     *
     * @param string|null $key
     * @param mixed $default
     *
     * @return mixed
     */
    protected function get($key = null, $default = null)
    {
        return $this->app['config']->get("{$this->configName()}.{$key}",
            $default);
    }

    /**
     * Get modules configuration
     *
     * @return array
     */
    public function modules()
    {
        return (array)$this->get('modules');
    }

    public function directory()
    {
        return $this->get('directory');
    }

    public function modulesNamespace()
    {
        return $this->get('namespace');
    }

    public function seederNamespace()
    {
        return $this->get('module_seeding.namespace');
    }

    public function seederFilename()
    {
        return $this->get('module_seeding.filename');
    }

    public function startSeparator()
    {
        return $this->get('separators.start');
    }

    public function endSeparator()
    {
        return $this->get('separators.end');
    }

    public function stubsDefaultGroup()
    {
        return $this->get('stubs.module_default_group');
    }

    public function filesStubsDefaultGroup()
    {
        return $this->get('stubs.files_default_group');
    }

    public function migrationDefaultType()
    {
        return $this->get('module_migrations.default_type');
    }

    public function migrationStubFileName($type)
    {
        return $this->get("module_migrations.types.{$type}");
    }

    public function stubsPath()
    {
        return $this->get('stubs.path');
    }

    public function stubGroupDirectory($group)
    {
        return $this->get("stubs_groups.{$group}.stub_directory", $group);
    }

    public function stubGroupDirectories($group)
    {
        return $this->get("stubs_groups.{$group}.directories", []);
    }

    public function stubGroupFiles($group)
    {
        return $this->get("stubs_groups.{$group}.files", []);
    }

    public function stubGroups()
    {
        return array_keys((array)$this->get('stubs_groups', []));
    }

    public function autoAdd()
    {
        return $this->get('module_make.auto_add', false);
    }

    public function autoAddPattern()
    {
        return $this->get('module_make.pattern');
    }

    public function autoAddTemplate()
    {
        return $this->get('module_make.module_template');
    }
}
