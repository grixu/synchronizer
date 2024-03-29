<?php

namespace Grixu\Synchronizer\Engine\Abstracts;

use Grixu\Synchronizer\Engine\Contracts\Engine;
use Grixu\Synchronizer\Engine\Contracts\EngineConfigInterface;
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
    protected string $key;
    protected string|null $checksum = null;

    public function __construct(EngineConfigInterface $config, protected Collection $input)
    {
        $this->key = $config->getKey();
        $this->checksum = $config->getChecksumField();
        $this->ids = collect();
        $this->model = new ($config->getModel());
        $this->modelKey = Str::snake($this->key);

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
                return (bool) ($this->model->{$relation}() instanceof BelongsToRelation)

                 ;
            })
            ->each(function ($relation) use ($transformer, &$allRelations) {
                $fieldName = $this->model->{$relation}()->getForeignKeyName();
                $transformer->getMap()->add($fieldName);
                $allRelations[$relation] = $fieldName;
            })
            ->toArray();

        return $allRelations;
    }
}
