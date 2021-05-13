<?php

namespace Grixu\Synchronizer\Abstracts;

use Grixu\Synchronizer\Contracts\Engine;
use Illuminate\Support\Collection;

abstract class BaseEngine implements Engine
{
    protected Collection $ids;

    public function __construct(protected Collection $input, protected string $key)
    {
        $this->ids = collect();

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
