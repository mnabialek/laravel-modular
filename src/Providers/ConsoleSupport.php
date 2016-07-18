<?php

namespace Mnabialek\LaravelSimpleModules\Providers;

use Illuminate\Foundation\Providers\ConsoleSupportServiceProvider;

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
        $customProviders = $this->app['simplemodule']->config('providers');

        return collect($this->providers)->map(
            function ($provider) use ($customProviders) {
                return isset($customProviders[$provider])
                    ? $customProviders[$provider] : $provider;
            })->all();
    }
}
