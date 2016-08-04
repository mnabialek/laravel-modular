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
        return $this->app['path.config'] . DIRECTORY_SEPARATOR .
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

    /**
     * Get directory where modules will be stored
     *
     * @return string
     */
    public function directory()
    {
        return $this->get('directory');
    }

    /**
     * Get namespace prefix for all modules
     *
     * @return string
     */
    public function modulesNamespace()
    {
        return $this->get('namespace');
    }

    /**
     * Get seeder namespace prefix
     *
     * @return string
     */
    public function seederNamespace()
    {
        return $this->get('module_seeding.namespace');
    }

    /**
     * Get seeder file
     *
     * @return string
     */
    public function seederFile()
    {
        return $this->get('module_seeding.file');
    }

    /**
     * Get service provider filename
     *
     * @return string
     */
    public function serviceProviderNamespace()
    {
        return $this->get('module_service_providers.namespace');
    }

    /**
     * Get namespace for controllers when loading routes
     *
     * @return string
     */
    public function routingControllerNamespace()
    {
        return $this->get('module_routing.route_group_namespace');
    }

    /**
     * Get routing file
     *
     * @return string
     */
    public function routingFile()
    {
        return $this->get('module_routing.file');
    }

    /**
     * Get factory file
     *
     * @return string
     */
    public function factoryFile()
    {
        return $this->get('module_factories.file');
    }

    /**
     * Get start separator for replacements
     *
     * @return string
     */
    public function startSeparator()
    {
        return $this->get('separators.start');
    }

    /**
     * Get end separator for replacements
     *
     * @return string
     */
    public function endSeparator()
    {
        return $this->get('separators.end');
    }

    /**
     * Get default stubs group name when creating module
     *
     * @return string
     */
    public function stubsDefaultGroup()
    {
        return $this->get('stubs.module_default_group');
    }

    /**
     * Get default stubs group name when creating module files
     *
     * @return string
     */
    public function filesStubsDefaultGroup()
    {
        return $this->get('stubs.files_default_group');
    }

    /**
     * Get default migration type
     *
     * @return string
     */
    public function migrationDefaultType()
    {
        return $this->get('module_migrations.default_type');
    }

    /**
     * Get stub name for migration of given type
     *
     * @param string $type
     *
     * @return string
     */
    public function migrationStubFileName($type)
    {
        return $this->get("module_migrations.types.{$type}");
    }

    /**
     * Get path where migrations are stored inside Module
     *
     * @return string
     */
    public function migrationsPath()
    {
        return $this->get('module_migrations.path');
    }

    /**
     * Get service provider file
     *
     * @return string
     */
    public function serviceProviderFile()
    {
        return $this->get('module_service_providers.file');
    }

    /**
     * Get path where stubs are located
     *
     * @return string
     */
    public function stubsPath()
    {
        return $this->get('stubs.path');
    }

    /**
     * Get relative directory for given stub group
     *
     * @param string $group
     *
     * @return string
     */
    public function stubGroupDirectory($group)
    {
        return $this->get("stubs_groups.{$group}.stub_directory", $group);
    }

    /**
     * Get directories to create for given stub group
     *
     * @param string $group
     *
     * @return array
     */
    public function stubGroupDirectories($group)
    {
        return (array)$this->get("stubs_groups.{$group}.directories", []);
    }

    /**
     * Get files to create for given stub group
     *
     * @param string $group
     *
     * @return array
     */
    public function stubGroupFiles($group)
    {
        return (array)$this->get("stubs_groups.{$group}.files", []);
    }

    /**
     * Get all existing stub groups
     *
     * @return array
     */
    public function stubGroups()
    {
        return array_keys((array)$this->get('stubs_groups', []));
    }

    /**
     * Get auto add to configuration status
     *
     * @return bool
     */
    public function autoAdd()
    {
        return (bool)$this->get('module_make.auto_add', false);
    }

    /**
     * Get auto add pattern
     *
     * @return string
     */
    public function autoAddPattern()
    {
        return $this->get('module_make.pattern');
    }

    /**
     * Get auto add template
     *
     * @return string
     */
    public function autoAddTemplate()
    {
        return $this->get('module_make.module_template');
    }
}
