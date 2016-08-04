<?php

namespace Mnabialek\LaravelModular\Providers;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Mnabialek\LaravelModular\Console\Commands\ModuleFiles;
use Mnabialek\LaravelModular\Console\Commands\ModuleMake;
use Mnabialek\LaravelModular\Console\Commands\ModuleMakeMigration;
use Mnabialek\LaravelModular\Console\Commands\ModuleSeed;
use Mnabialek\LaravelModular\Services\Config;
use Mnabialek\LaravelModular\Services\Modular;

class ModularServiceProvider extends ServiceProvider
{
    /**
     * @var Collection|array
     */
    protected $filesToPublish = [];

    /**
     * @var Config
     */
    protected $config;

    /**
     * Create a new service provider instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application $app
     */
    public function __construct($app)
    {
        parent::__construct($app);
        $this->config = $app->make(Config::class);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // register module binding
        $this->app->singleton('modular', function ($app) {
            return new Modular($app, $this->config);
        });

        $this->app->singleton('modular.config', function ($app) {
            return new Config($app);
        });

        // register new Artisan commands
        $this->commands([
            ModuleMake::class,
            ModuleSeed::class,
            ModuleMakeMigration::class,
            ModuleFiles::class,
        ]);

        // register files to be published
        $this->publishes($this->getFilesToPublish()->all());

        // set migrations paths
        $this->setModulesMigrationPaths();

        // register modules providers
        $this->app['modular']->loadServiceProviders();
    }

    /**
     * {@inheritdoc}
     */
    public function provides()
    {
        return ['modular', 'modular.config'];
    }

    /**
     * Get files that should be published
     *
     * @return Collection
     */
    protected function getFilesToPublish()
    {
        $this->filesToPublish = collect();

        $this->addConfigurationToPublished()
            ->addStubsTemplatesToPublished()
            ->addAppFilesToPublished();

        return $this->filesToPublish;
    }

    /**
     * Add configuration file to published files
     *
     * @return $this
     */
    protected function addConfigurationToPublished()
    {
        $configName = $this->config->configName();
        $this->filesToPublish->put($this->getDefaultConfigFilePath($configName),
            $this->config->configPath());

        return $this;
    }

    /**
     * Add stubs templates to published files
     *
     * @return $this
     */
    protected function addStubsTemplatesToPublished()
    {
        $templatesPath = $this->getTemplatesStubsPath();
        $pathLength = mb_strlen($templatesPath);

        // here we get all stubs files from stubs templates directory
        $publishedStubsPath = $this->config->stubsPath();
        collect(glob($templatesPath . '/*/{,.}*.stub', GLOB_BRACE))
            ->each(function ($file) use ($publishedStubsPath, $pathLength) {
                $this->filesToPublish->put($file,
                    $publishedStubsPath . DIRECTORY_SEPARATOR .
                    mb_substr($file, $pathLength + 1));
            });

        return $this;
    }

    /**
     * Add app files to published files
     *
     * @return $this
     */
    protected function addAppFilesToPublished()
    {
        $appPath = $this->getAppSamplePath();
        collect(glob($appPath . '/*/*'))->each(function ($file) use ($appPath) {
            $this->filesToPublish->put($file,
                $this->app['path'] . DIRECTORY_SEPARATOR .
                mb_substr($file, mb_strlen($appPath) + 1));

        });

        return $this;
    }

    /**
     * Get stub templates directory
     *
     * @return string
     */
    protected function getTemplatesStubsPath()
    {
        return realpath(__DIR__ . '/../../stubs/templates/');
    }

    /**
     * Get default configuration file path
     *
     * @return string
     */
    public function getDefaultConfigFilePath($configName)
    {
        return realpath(__DIR__ . "/../../config/{$configName}.php");
    }

    /**
     * Get sample app path
     *
     * @return string
     */
    protected function getAppSamplePath()
    {
        return realpath(__DIR__ . '/../../stubs/app');
    }

    /**
     * Set migrations paths for all active modules
     */
    protected function setModulesMigrationPaths()
    {
        $paths = collect();

        // add to paths all migration directories from modules
        collect($this->app['modular']->active())
            ->each(function ($module) use ($paths) {
                $paths->push($module->getMigrationsPath());
            });

        $this->loadMigrationsFrom($paths->all());
    }
}
