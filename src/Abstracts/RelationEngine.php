<?php

namespace Grixu\Synchronizer\Abstracts;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use ReflectionClass;

abstract class RelationEngine extends BaseEngine
{
    public Collection $loaded;
    public Model $model;

    public function __construct(protected Collection $input, protected string $key, string $model)
    {
        parent::__construct($input, $key);
        $this->loaded = collect();
        $this->model = new $model;

        $this->checkRelations();

        $this->input = $this->filter($this->input);

        if ($this->input->count() > 0) {
            $this->load();
        }
    }

    protected function checkRelations()
    {
        $test = $this->model;
        $reflection = new ReflectionClass($test);

        $this->input->pluck('relations')
            ->flatten(1)
            ->filter()
            ->pluck('relation', 'foreignClass')
            ->unique()
            ->each(
                function ($relation, $class) use ($test, $reflection) {
                    if (!$reflection->hasMethod($relation)) {
                        throw new Exception('Relation ' . $relation . ' do not exist!');
                    }

                    if (!$test->$relation() instanceof Relation) {
                        throw new Exception($relation . ' in ' . $this->model::class . ' is not relation');
                    }

                    if (!$test->$relation()->getRelated() instanceof $class) {
                        throw new Exception(
                            $relation . ' in ' . $this->model::class . ' is not related with ' . $class
                        );
                    }
                }
            );
    }

    abstract protected function filter(Collection $dataSet): Collection;

    protected function load()
    {
        $this->loaded = collect();

        $this->input->pluck('relations')
            ->flatten(1)
            ->filter()
            ->groupBy('relation')
            ->filter()
            ->each(
                function ($collection, $relation) {
                    $data = collect();
                    $model = $this->model->$relation()->getRelated();

                    $collection->groupBy('foreignField')
                        ->filter()
                        ->each(
                            function ($collection, $foreignField) use ($data, $relation, $model) {
                                $foreignKeys = $collection->pluck('foreignKeys')->flatten(1);

                                $data->put(
                                    $foreignField,
                                    $model::query()
                                        ->whereIn($foreignField, $foreignKeys)
                                        ->pluck('id', $foreignField)
                                );
                            }
                        );

                    $this->loaded->put($relation, $data);
                }
            );
    }
}
