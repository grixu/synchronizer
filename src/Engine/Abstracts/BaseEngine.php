<?php

namespace Grixu\Synchronizer\Engine\Abstracts;

use Grixu\Synchronizer\Engine\Contracts\Engine;
use Grixu\Synchronizer\Engine\Contracts\Transformer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo as BelongsToRelation;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

abstract class BaseEngine implements Engine
{
    protected Collection $ids;
    protected Model $model;
    protected string $modelKey;

    public function __construct(protected Collection $input, protected string $key, string $model)
    {
        $this->ids = collect();
        $this->model = new $model;
        $this->modelKey = Str::snake($key);

        $this->filterByKeyExistence();
    }

    protected function filterByKeyExistence()
    {
        $key = $this->key;
        $this->input = $this->input->filter(fn ($item) => isset($item[$key]));
    }

    public function getIds(): Collection
    {
        return $this->ids;
    }

    protected function getAllRelations(Transformer $transformer): array
    {
        $allRelations = [];
        $this->input->pluck('relations.*.relation')
            ->flatten()
            ->filter()
            ->filter(function ($relation) {
                if ($this->model->$relation() instanceof BelongsToRelation) {
                    return true;
                }

                return false;
            })
            ->each(function ($relation) use ($transformer, &$allRelations) {
                $fieldName = $this->model->$relation()->getForeignKeyName();
                $transformer->getMap()->add($fieldName);
                $allRelations[$relation] = $fieldName;
            })
            ->toArray();

        return $allRelations;
    }
}
