<?php

namespace Grixu\Synchronizer\Abstracts;

use Exception;
use Grixu\Synchronizer\Contracts\Engine as EngineInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use ReflectionClass;

abstract class RelationEngine implements EngineInterface
{
    public Collection $input;
    public Collection $loaded;
    public Collection $ids;
    public Model $model;

    public function __construct(string $model, Collection $dataSet)
    {
        $this->ids = collect();
        $this->loaded = collect();
        $this->model = new $model;

        $this->check($dataSet);

        $this->input = $this->filter($dataSet);

        if ($this->input->count() > 0) {
            $this->load();
        }
    }

    protected function check(Collection $input)
    {
        $test = $this->model;
        $reflection = new ReflectionClass($test);

        $input->pluck('relations')
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

    public function getIds(): Collection
    {
        return $this->ids;
    }
}
