<?php

namespace App\Core;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Container\Container;

abstract class AbstractRepository
{
    /**
     * @var Container
     */
    protected $app;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var Builder
     */
    protected $workingModel;

    /**
     * AbstractRepository constructor.
     *
     * @param Container $app
     * @param Model $model
     */
    public function __construct(Container $app, Model $model)
    {
        $this->app = $app;
        $this->model = $model;
    }

    /**
     * Find a model by its primary key. If none found, throw an exception
     *
     * @param mixed $id
     * @param array $columns
     *
     * @return Collection|Model
     */
    public function findOrFail($id, array $columns = ['*'])
    {
        return $this->getWorkingModel()->findOrFail($id, $columns);
    }

    /**
     * Find a model by its primary key
     *
     * @param mixed $id
     * @param array $columns
     *
     * @return Collection|Model
     */
    public function find($id, array $columns = ['*'])
    {
        return $this->getWorkingModel()->find($id, $columns);
    }

    /**
     * Get model
     *
     * @return Model
     */
    protected function getModel()
    {
        return $this->model;
    }

    /**
     * Create a new instance of model
     *
     * @param array $attributes
     *
     * @return Model
     */
    public function newInstance(array $attributes = [])
    {
        return $this->getModel()->newInstance($attributes);
    }

    /**
     * Save a new model and return the instance.
     *
     * @param array $attributes
     *
     * @return Model
     */
    public function create(array $attributes = [])
    {
        return $this->getModel()->create($attributes);
    }

    /**
     * Save a new model and return the instance. Allow mass-assignment.
     *
     * @param array $attributes
     *
     * @return Model
     */
    public function forceCreate(array $attributes = [])
    {
        return $this->getModel()->forceCreate($attributes);
    }

    /**
     * Destroy the models for the given primary keys.
     *
     * @param array|int $ids
     *
     * @return bool
     */
    public function destroy($ids)
    {
        return $this->getWorkingModel()->destroy($ids);
    }

    /**
     * Sets working model
     *
     * @param Builder $model
     */
    public function setWorkingModel(Builder $model)
    {
        $this->workingModel = $model;
    }

    /**
     * Clears working model
     */
    public function clearWorkingModel()
    {
        $this->workingModel = null;
    }

    /**
     * Get working model (if none set it will return model)
     *
     * @return Model
     */
    protected function getWorkingModel()
    {
        return $this->workingModel ?: $this->model;
    }
}
