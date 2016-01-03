<?php

namespace App\Core;

use Illuminate\Contracts\Container\Container;

abstract class Service
{
    /**
     * @var Container
     */
    protected $app;

    /**
     * @var AbstractRepository
     */
    protected $repo;

    /**
     * Service constructor.
     *
     * @param Container $app
     * @param AbstractRepository $repo
     */
    public function __construct(Container $app, AbstractRepository $repo)
    {
        $this->app = $app;
        $this->repo = $repo;
    }
}
