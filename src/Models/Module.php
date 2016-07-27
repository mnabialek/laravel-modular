<?php

namespace Mnabialek\LaravelModular\Models;

use Mnabialek\LaravelModular\Services\Config;
use Mnabialek\LaravelModular\SimpleModule;
use Mnabialek\LaravelModular\Traits\Normalizer;
use Mnabialek\LaravelModular\Traits\Replacer;

class Module
{
    use Normalizer, Replacer;

    /**
     * @var
     */
    protected $name;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var
     */
    protected $config;

    /**
     * Module constructor.
     *
     * @param string $name
     * @param array $options
     * @param Config $config
     */
    public function __construct($name, Config $config, array $options = [])
    {
        $this->name = $name;
        $this->options = collect($options);
        $this->config = $config;
    }

    /**
     * Get module name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * Get module seeder class name (with namespace)
     *
     * @param string|null $class
     *
     * @return string
     */
    public function getSeederClass($class = null)
    {
        $name = $this->getName();

        $class = $class ?: basename($this->config->getSeederFilename(), '.php');

        return $this->replace($this->config->getNamespace() . '\\' . $name .
            '\\' . $this->config->getSeederNamespace() . '\\' . $class, $name);
    }
    
    
    

    public function getDirectory()
    {
        return $this->normalizePath($this->modules->config('directory')) .
        DIRECTORY_SEPARATOR . $this->getName();
    }

    public function getMigrationsPath()
    {
        return $this->getDirectory() . DIRECTORY_SEPARATOR .
        $this->normalizePath($this->modules->config('module_migrations.path'));
    }

    public function getServiceProviderClass()
    {
        $class =
            basename($this->modules->config('module_service_providers.filename'),
                '.php');

        return $this->replace($this->modules->config('namespace') . '\\' .
            $this->getName() . '\\'
            . ($this->config('module_service_providers.namespace')) . '\\' .
            $class,
            $this->getName());
    }

    public function hasProvider()
    {
        return array_key_exists('provider', $this->options) ?
            (bool)$this->options['provider'] :
            file_exists($this->getServiceProviderClass());
    }

    /**
     * Verifies whether given module has factory
     *
     * @param string $module
     * @param null $options
     *
     * @return bool
     */
    protected function hasFactory()
    {
        return array_key_exists('factories', $this->options) ?
            (bool)$this->options['factories'] :
            file_exists($this->getFactoryFilePath());
    }

    /**
     * Get controller namespace for module
     *
     * @param string $module
     *
     * @return string
     */
    public function getRouteControllerNamespace()
    {
        return $this->modules->config('namespace', '') . '\\' .
        $this->getName() . '\\' .
        $this->modules->config('module_routing.route_group_namespace', '');
    }

    /**
     * Get module routes file (with path)
     *
     * @param $module
     *
     * @return string
     */
    public function getRoutesFilePath()
    {
        return $this->getDirectory() .
        DIRECTORY_SEPARATOR . $this->modules->config('module_routing.file');
    }

    /**
     * Get module factories file (with path)
     *
     * @param $module
     *
     * @return string
     */
    public function getFactoryFilePath()
    {
        return $this->getDirectory() . DIRECTORY_SEPARATOR .
        $this->replace($this->modules->config('module_factories.file'),
            $this->getName());
    }

    /**
     * Get module service provider file (with path)
     *
     * @param $module
     *
     * @return string
     */
    public function getServiceProviderFilePath()
    {
        return $this->getDirectory() . DIRECTORY_SEPARATOR .
        $this->normalizePath($this->modules->config('module_service_providers.path')) .
        DIRECTORY_SEPARATOR .
        $this->replace($this->modules->config('module_service_providers.filename'),
            $this->getName());
    }



    /**
     * Get seeder filename (with path) for module
     *
     * @param string $module
     *
     * @return string
     */
    protected function getSeederFile($module)
    {
        return $this->getSeederDirectory($module) . DIRECTORY_SEPARATOR .
        $this->replace($this->config('module_seeding.filename'), $module);
    }

    /**
     * Get full seeder directory for given module
     *
     * @param string $module
     *
     * @return string
     */
    protected function getSeederDirectory($module)
    {
        return $this->getDirectory() . DIRECTORY_SEPARATOR .
        $this->config('module_seeding.path');
    }

    /**
     * Verifies whether given module is active
     *
     * @param string $module
     * @param null $options
     *
     * @return bool
     */
    protected function isActive($options = null)
    {
        if (is_array($options)) {
            return array_key_exists('active', $options) ?
                (bool)$options['active'] : true;
        }
    }

    /**
     * Verifies whether given module has routes
     *
     * @param string $module
     * @param null $options
     *
     * @return bool
     */
    protected function hasRoutes($module, $options = null)
    {
        if (is_array($options)) {
            return array_key_exists('routes', $options) ?
                (bool)$options['routes'] :
                file_exists($this->getModuleRoutesFilePath($module));
        }
    }
}
