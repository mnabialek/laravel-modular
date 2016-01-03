<?php

namespace Mnabialek\LaravelSimpleModules;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Database\Seeder;
use Illuminate\Routing\Router;
use Mnabialek\LaravelSimpleModules\Traits\Normalizer;
use Mnabialek\LaravelSimpleModules\Traits\Replacer;

class SimpleModule
{
    use Replacer;
    use Normalizer;

    /**
     * @var Container
     */
    protected $app;

    /**
     * Name for module config file (without .php extension)
     *
     * @var string
     */
    protected $configName = 'simplemodules';

    /**
     * SimpleModule constructor.
     *
     * @param Container $app
     */
    public function __construct(Container $app)
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
    public function config($key = null, $default = null)
    {
        return $this->app->config->get("{$this->getConfigName()}.{$key}",
            $default);
    }

    /**
     * Runs main seeders for all active modules
     *
     * @param Seeder $seeder
     */
    public function seed(Seeder $seeder)
    {
        $modules = $this->active();

        foreach ($modules as $module) {
            $seederFile = $this->getSeederFile($module);

            if (file_exists($seederFile)) {
                $seeder->call($this->getSeederClass($module));
            }
        }
    }

    /**
     * Get module seeder class name (with namespace)
     *
     * @param string $module
     * @param string|null $class
     *
     * @return string
     */
    public function getSeederClass($module, $class = null)
    {
        $module = $this->getModuleName($module);

        $class = $class ?: basename($this->config('module_seeding.filename'),
            '.php');

        return $this->replace($this->config('namespace') . '\\' . $module .
            '\\' . $this->config('module_seeding.namespace') . '\\' .
            $class, $module);
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
        return $this->getModuleDirectory($module) . DIRECTORY_SEPARATOR .
        $this->config('module_seeding.path');
    }

    /**
     * Load routes for active modules
     *
     * @param Registrar $router
     */
    public function loadRoutes(Registrar $router)
    {
        $modules = $this->routable();

        foreach ($modules as $module) {
            $router->group(['namespace' => $this->getRouteControllerNamespace($module)],
                function ($router) use ($module) {
                    require(base_path($this->getModuleRoutesFilePath($module)));
                });
        }
    }

    /**
     * Load factories for active modules
     */
    public function loadFactories($factory)
    {
        $modules = $this->withFactories();

        foreach ($modules as $module) {
            require($this->getModuleFactoryFilePath($module));
        }
    }

    /**
     * Load service providers for active modules
     */
    public function loadServiceProviders()
    {
        $modules = $this->withServiceProviders();

        foreach ($modules as $module) {
            $this->app->register($this->getServiceProviderClass($module));
        }
    }

    /**
     * Get controller namespace for module
     *
     * @param string $module
     *
     * @return string
     */
    protected function getRouteControllerNamespace($module)
    {
        return $this->config('namespace', '') . '\\' . $module . '\\' .
        $this->config('module_routing.route_group_namespace', '');
    }

    /**
     * Get module routes file (with path)
     *
     * @param $module
     *
     * @return string
     */
    protected function getModuleRoutesFilePath($module)
    {
        return $this->getModuleDirectory($module) .
        DIRECTORY_SEPARATOR . $this->config('module_routing.file');
    }

    /**
     * Get module factories file (with path)
     *
     * @param $module
     *
     * @return string
     */
    protected function getModuleFactoryFilePath($module)
    {
        return $this->getModuleDirectory($module) . DIRECTORY_SEPARATOR .
        $this->replace($this->config('module_factories.file'), $module);
    }

    /**
     * Get module service provider file (with path)
     *
     * @param $module
     *
     * @return string
     */
    protected function getModuleServiceProviderFilePath($module)
    {
        return $this->getModuleDirectory($module) . DIRECTORY_SEPARATOR .
        $this->normalizePath($this->config('module_service_providers.path')) .
        DIRECTORY_SEPARATOR .
        $this->replace($this->config('module_service_providers.filename'),
            $module);
    }

    /**
     * Get all routable modules (active and having routes file)
     *
     * @return array
     */
    public function routable()
    {
        $list = [];

        $modules = $this->modules();

        foreach ($modules as $module => $options) {
            if ($this->isActive($module, $options) &&
                $this->hasRoutes($module, $options)
            ) {
                $list[] = $module;
            }
        }

        return $list;
    }

    /**
     * Get all routable modules (active and having routes file)
     *
     * @return array
     */
    public function withFactories()
    {
        $list = [];

        $modules = $this->modules();

        foreach ($modules as $module => $options) {
            if ($this->isActive($module, $options) &&
                $this->hasFactory($module, $options)
            ) {
                $list[] = $module;
            }
        }

        return $list;
    }

    /**
     * Get all modules that have service providers (active and having service
     * provider file)
     *
     * @return array
     */
    public function withServiceProviders()
    {
        $list = [];

        $modules = $this->modules();

        foreach ($modules as $module => $options) {
            if ($this->isActive($module, $options) &&
                $this->hasServiceProvider($module, $options)
            ) {
                $list[] = $module;
            }
        }

        return $list;
    }

    /**
     * Get all modules from configuration file
     *
     * @return array
     */
    protected function modules()
    {
        return $this->config('modules', []);
    }

    /**
     * Get active modules
     *
     * @return array
     */
    public function active()
    {
        $list = [];

        $modules = $this->modules();

        foreach ($modules as $module => $options) {
            if ($this->isActive($module, $options)) {
                $list[] = $module;
            }
        }

        return $list;
    }

    /**
     * Get all modules
     *
     * @return array
     */
    public function all()
    {
        return array_keys($this->modules());
    }

    /**
     * Get disabled modules
     *
     * @return array
     */
    public function disabled()
    {
        return array_diff($this->all(), $this->active());
    }

    /**
     * Verifies whether given module is active
     *
     * @param string $module
     * @param null $options
     *
     * @return bool
     */
    protected function isActive($module, $options = null)
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

    /**
     * Verifies whether given module has factory
     *
     * @param string $module
     * @param null $options
     *
     * @return bool
     */
    protected function hasFactory($module, $options = null)
    {
        if (is_array($options)) {
            return array_key_exists('factories', $options) ?
                (bool)$options['factories'] :
                file_exists($this->getModuleFactoryFilePath($module));
        }
    }

    /**
     * Verifies whether given module has service provider
     *
     * @param string $module
     * @param null $options
     *
     * @return bool
     */
    protected function hasServiceProvider($module, $options = null)
    {
        echo $this->getModuleServiceProviderFilePath($module) . "\n";
        if (is_array($options)) {
            return array_key_exists('provider', $options) ?
                (bool)$options['provider'] :
                file_exists($this->getModuleServiceProviderFilePath($module));
        }
    }

    /**
     * Verifies whether given module exists
     *
     * @param string $module
     *
     * @return bool
     */
    public function exists($module)
    {
        return in_array($this->getModuleName($module), $this->all());
    }

    /**
     * Get module name (normalized or not depending on settings)
     *
     * @param $module
     *
     * @return string
     */
    public function getModuleName($module)
    {
        $normalize = $this->config('normalize_module_name', true);

        if (!$normalize) {
            return $module;
        }

        return studly_case($module);
    }

    /**
     * Get directory for given module
     *
     * @param string $module
     *
     * @return string
     */
    public function getModuleDirectory($module)
    {
        return $this->normalizePath($this->config('directory')) .
        DIRECTORY_SEPARATOR . $this->getModuleName($module);
    }

    /**
     * Get module migration path
     *
     * @param string $module
     *
     * @return string
     */
    public function getMigrationsPath($module)
    {
        return $this->getModuleDirectory($module) . DIRECTORY_SEPARATOR .
        $this->normalizePath($this->config('module_migrations.path'));
    }

    /**
     * Get service provider class (with namespace)
     *
     * @param string $module
     *
     * @return string
     */
    protected function getServiceProviderClass($module)
    {
        $class = basename($this->config('module_service_providers.filename'),
            '.php');

        return $this->replace($this->config('namespace') . '\\' . $module . '\\'
            . ($this->config('module_service_providers.namespace')) . '\\' .
            $class,
            $module);
    }
}
