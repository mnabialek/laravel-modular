<?php

namespace Mnabialek\LaravelSimpleModules\Providers;

use Illuminate\Support\ServiceProvider;
use Mnabialek\LaravelSimpleModules\Console\Commands\ModuleFiles;
use Mnabialek\LaravelSimpleModules\Console\Commands\ModuleMake;
use Mnabialek\LaravelSimpleModules\Console\Commands\ModuleMakeMigration;
use Mnabialek\LaravelSimpleModules\Console\Commands\ModuleMigrate;
use Mnabialek\LaravelSimpleModules\Console\Commands\ModuleSeed;
use Mnabialek\LaravelSimpleModules\SimpleModule;

class SimpleModules extends ServiceProvider
{
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

        // register new artisan commands
        $this->commands([
            ModuleMake::class,
            ModuleMigrate::class,
            ModuleSeed::class,
            ModuleMakeMigration::class,
            ModuleFiles::class,
        ]);

        // register files to be published
        $this->publishes($this->getFilesToPublish());

        // register module providers
        $this->registerModulesProviders();
    }

    /**
     * {@inheritdoc}
     */
    public function provides()
    {
        return ['simplemodule'];
    }

    /**
     * Register service providers for modules
     */
    protected function registerModulesProviders()
    {
        /** @var SimpleModule $simpleModule */
        $simpleModule = $this->app['simplemodule'];
        $simpleModule->loadServiceProviders();
    }

    /**
     * Get files that should be published
     *
     * @return array
     */
    protected function getFilesToPublish()
    {
        /** @var SimpleModule $simpleModule */
        $simpleModule = $this->app['simplemodule'];
        $configName = $simpleModule->getConfigName();

        $publishes = [
            // configuration file
            $this->getDefaultConfigFilePath($configName)
            => $simpleModule->getConfigFilePath(),
        ];

        // stubs files
        $templatesPath = $this->getTemplatesStubsPath();
        $pathLength = mb_strlen($templatesPath);
        // here we get all stubs files from stubs templates directory
        $files = glob($templatesPath . '/*/{,.}*.stub', GLOB_BRACE);

        $publishedStubsPath = $simpleModule->config('stubs.path');
        foreach ($files as $file) {
            $publishes[$file] = $publishedStubsPath . DIRECTORY_SEPARATOR .
                mb_substr($file, $pathLength + 1);
        }

        // sample app files
        $appPath = $this->getAppSamplePath();
        $files = glob($appPath . '/*/*');
        foreach ($files as $file) {
            $publishes[$file] =
                app_path(mb_substr($file, mb_strlen($appPath) + 1));
        }

        return $publishes;
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
