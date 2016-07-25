<?php

namespace Mnabialek\LaravelModular\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Mnabialek\LaravelModular\Console\Traits\ModuleCreator;

abstract class BaseCommand extends Command
{
    use ModuleCreator;

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
