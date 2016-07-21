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
    
}
