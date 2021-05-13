<?php

namespace Grixu\Synchronizer\Abstracts;

use Grixu\Synchronizer\Contracts\Engine;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class BaseEngine implements Engine
{
    protected Collection $ids;
    protected Model $model;


    public function __construct(protected Collection $input, protected string $key, string $model)
    {
        $this->ids = collect();
        $this->model = new $model;

        $this->filterByKeyExistence();
    }

    protected function filterByKeyExistence()
    {
        $key = $this->key;
        $this->input = $this->input->filter(fn ($item) => !empty($item[$key]));
    }

    public function getIds(): Collection
    {
        return $this->ids;
    }
}
