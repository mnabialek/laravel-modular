<?php

namespace Mnabialek\LaravelSimpleModules\Models;

use Illuminate\Contracts\Foundation\Application;

class Config
{
    /**
     * Name for module config file (without .php extension)
     *
     * @var string
     */
    protected $configName = 'simplemodules';

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
    public function getConfigName()
    {
        return $this->configName;
    }

    /**
     * Get user configuration file path
     *
     * @return string
     */
    public function getConfigFilePath()
    {
        return config_path("{$this->getConfigName()}.php");
    }

    /**
     * Get value from module configuration file
     *
     * @param string|null $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function get($key = null, $default = null)
    {
        return $this->app['config']->get("{$this->getConfigName()}.{$key}",
            $default);
    }

    /**
     * Get modules configuration
     *
     * @return array
     */
    public function getModules()
    {
        return (array)$this->get('modules');
    }

    public function getDirectory()
    {
        return $this->get('directory');
    }

    public function getNamespace()
    {
        return $this->get('namespace');
    }

    public function getSeederNamespace()
    {
        return $this->get('module_seeding.namespace');
    }

    public function getSeederFilename()
    {
        return $this->get('module_seeding.filename');
    }

    public function getStartSeparator()
    {
        return $this->get('separators.start');
    }

    public function getEndSeparator()
    {
        return $this->get('separators.end');
    }

    public function getStubsDefaultGroup()
    {
        return $this->get('stubs.module_default_group');
    }

    public function getFilesStubsDefaultGroup()
    {
        return $this->get('stubs.files_default_group');
    }    

    public function getMigrationDefaultType()
    {
        return $this->get('module_migrations.default_type');
    }

    public function getMigrationStubFileName($type)
    {
        return $this->get("module_migrations.types.{$type}");
    }

    public function getStubsPath()
    {
        return $this->get('stubs.path');
    }

    public function getStubGroupDirectory($group)
    {
        return $this->get("stubs_groups.{$group}.stub_directory", $group);
    }

    public function getStubGroupDirectories($group)
    {
        return $this->get("stubs_groups.{$group}.directories", []);
    }

    public function getStubGroupFiles($group)
    {
        return $this->get("stubs_groups.{$group}.files", []);
    }

    public function autoAdd()
    {
        return $this->get("module_make.auto_add", false);
    }

    public function autoAddPattern()
    {
        return $this->get("module_make.pattern");
    }

    public function autoAddTemplate()
    {
        return $this->get("module_make.module_template");
    }    
}
