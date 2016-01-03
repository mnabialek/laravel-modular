<?php

namespace Mnabialek\LaravelSimpleModules\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\Container;
use Mnabialek\LaravelSimpleModules\Console\Traits\ModuleCreator;
use Mnabialek\LaravelSimpleModules\SimpleModule;

abstract class BaseCommand extends Command
{
    use ModuleCreator;

    /**
     * @var Container
     */
    protected $app;

    /**
     * @var SimpleModule
     */
    protected $module;

    /**
     * Create a new command instance.
     *
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        parent::__construct();
        $this->app = $app;
        $this->module = $this->app['simplemodule'];
    }

    /**
     * Run commands
     */
    public function handle()
    {
        try {
            // verify whether module config file exists
            $this->verifyConfigExistence();

            $this->proceed();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * Main command function that launches all the logic
     */
    abstract protected function proceed();
}
