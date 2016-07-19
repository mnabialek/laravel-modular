<?php

namespace Mnabialek\LaravelSimpleModules\Providers;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Mnabialek\LaravelSimpleModules\Console\Commands\ModuleFiles;
use Mnabialek\LaravelSimpleModules\Console\Commands\ModuleMake;
use Mnabialek\LaravelSimpleModules\Console\Commands\ModuleMakeMigration;
use Mnabialek\LaravelSimpleModules\Console\Commands\ModuleSeed;
use Mnabialek\LaravelSimpleModules\SimpleModule;

class SimpleModules extends ServiceProvider
{
    /**
     * @var Collection|array
     */
    protected $filesToPublish = [];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // register module binding
        $this->app->bind('simplemodule', function ($app) {
            return new SimpleModule($app);
        }, true);

        // register new Artisan commands
        $this->commands([
            ModuleMake::class,
            ModuleSeed::class,
            ModuleMakeMigration::class,
            ModuleFiles::class,
        ]);

        // register files to be published
        $this->publishes($this->getFilesToPublish()->all());

        // register modules providers
        $this->app['simplemodule']->loadServiceProviders();
    }

    /**
     * {@inheritdoc}
     */
    public function provides()
    {
        return ['simplemodule'];
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
        $configName = $this->app['simplemodule']->getConfigName();
        $this->filesToPublish->put($this->getDefaultConfigFilePath($configName),
            $this->app['simplemodule']->getConfigFilePath());

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
        $publishedStubsPath = $this->app['simplemodule']->config('stubs.path');
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
                $this->app['path'] . mb_substr($file, mb_strlen($appPath) + 1));

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
}
