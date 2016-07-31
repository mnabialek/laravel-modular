<?php

namespace Mnabialek\LaravelModular\Models;

use Illuminate\Contracts\Foundation\Application;
use Mnabialek\LaravelModular\Services\Config;
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
     * @var Config
     */
    protected $config;

    /**
     * @var Application
     */
    protected $laravel;

    /**
     * Module constructor.
     *
     * @param string $name
     * @param Application $application
     * @param array $options
     */
    public function __construct($name, Application $application, array $options = [])
    {
        $this->name = $name;
        $this->options = collect($options);
        $this->laravel = $application;
        $this->config = $application['modular.config'];
    }

    /**
     * Get module name
     *
     * @return string
     */
    public function name()
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
    public function seederClass($class = null)
    {
        $name = $this->name();

        $class = $class ?: basename($this->config->seederFilename(), '.php');

        return $this->replace($this->config->modulesNamespace() . '\\' . $name .
            '\\' . $this->config->seederNamespace() . '\\' . $class, $this);
    }

    /**
     * Get module directory
     *
     * @return string
     */
    public function directory()
    {
        return $this->normalizePath($this->config->directory()) .
        DIRECTORY_SEPARATOR . $this->name();
    }

    /**
     * Get module migrations path
     *
     * @return string
     */
    public function migrationsPath()
    {
        return $this->normalizePath($this->directory()) . DIRECTORY_SEPARATOR .
        $this->normalizePath($this->config->migrationsPath());
    }

    /**
     * Get module service provider class
     *
     * @return string
     */
    public function serviceProviderClass()
    {
        $class = basename($this->config->serviceProviderFilename(), '.php');

        return $this->replace($this->config->modulesNamespace() . '\\' .
            $this->name() . '\\' . $this->config->serviceProviderNamespace() .
            '\\' . $class, $this);
    }

    /**
     * Verify whether module has service provider
     *
     * @return bool
     */
    public function hasProvider()
    {
        return $this->hasFile('provider', 'serviceProviderClass');
    }

    /**
     * Verifies whether module has factory
     *
     * @return bool
     */
    public function hasFactory()
    {
        return $this->hasFile('factory', 'factoryClass');
    }

    /**
     * Verifies whether module has routes file
     *
     * @return bool
     */
    public function hasRoutes()
    {
        return $this->hasFile('routes', 'routesPath');
    }

    /**
     * Verifies whether module has file of given type either checking config
     * and if it's not exist by checking whether file exists
     *
     * @param string $option
     * @param string $pathFunction
     *
     * @return bool
     */
    protected function hasFile($option, $pathFunction)
    {
        return (bool)($this->options->has($option) ? $this->options->get($option) :
            $this->laravel['files']->exists($this->$pathFunction()));
    }

    /**
     * Get controller namespace
     *
     * @return string
     */
    public function routeControllerNamespace()
    {
        return $this->modules->config('namespace', '') . '\\' .
        $this->getName() . '\\' .
        $this->modules->config('module_routing.route_group_namespace', '');
    }

    /**
     * Get module routes file (with path)
     *
     * @return string
     */
    public function routesFilePath()
    {
        return $this->getDirectory() .
        DIRECTORY_SEPARATOR . $this->modules->config('module_routing.file');
    }

    /**
     * Get module factory file path
     *
     * @return string
     */
    public function factoryFilePath()
    {
        return $this->getDirectory() . DIRECTORY_SEPARATOR .
        $this->replace($this->modules->config('module_factories.file'),
            $this->getName());
    }

    /**
     * Get module service provider file (with path)
     *
     * @return string
     */
    public function serviceProviderFilePath()
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
    protected function seederFile($module)
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
    public function isActive($options = null)
    {
        if (is_array($options)) {
            return array_key_exists('active', $options) ?
                (bool)$options['active'] : true;
        }
    }
}
