<?php

namespace Mnabialek\LaravelSimpleModules\Providers;

use Illuminate\Foundation\Providers\ConsoleSupportServiceProvider;
use Mnabialek\LaravelSimpleModules\SimpleModule;

class ConsoleSupport extends ConsoleSupportServiceProvider
{
    /**
     * ConsoleSupport constructor. Modifies default providers
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    public function __construct($app)
    {
        parent::__construct($app);
        // modify default providers
        $this->providers = $this->getCustomProviders();
    }

    /**
     * Get providers that will be used
     *
     * @return array
     */
    protected function getCustomProviders()
    {
        /** @var SimpleModule $modules */
        $modules = $this->app->make(SimpleModule::class);

        $providers = $modules->config('providers');

        return array_map(function ($v) use ($providers) {
            return isset($providers[$v]) ? $providers[$v] : $v;
        }, $this->providers);
    }
}
