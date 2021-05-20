<?php

namespace Grixu\Synchronizer\Process\Abstracts;

use Grixu\Synchronizer\Process\Contracts\LoaderInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

abstract class AbstractLoader implements LoaderInterface
{
    protected int $count = 0;
    protected Builder $query;
    protected Collection $data;

    public function getCount(): int
    {
        $this->checkIsDataLoaded();

        return $this->count;
    }

    protected function checkIsDataLoaded(): void
    {
        if ($this->count <= 0) {
            if (empty($this->query)) {
                $this->buildQuery();
            }

            $this->data = $this->query->get();
            $this->count = $this->data->count();
        }
    }

    public function chunk(\Closure $loop): void
    {
        if (empty($this->query)) {
            $this->buildQuery();
        }

        $this->query->chunk(config('synchronizer.sync.default_chunk_size'), $loop);
    }

    public function get(): Collection
    {
        $this->checkIsDataLoaded();

        return $this->data->chunk(config('synchronizer.sync.default_chunk_size'));
    }

    public function getRaw(): Collection
    {
        $this->checkIsDataLoaded();

        return $this->data;
    }

    public function getBuilder(): Builder
    {
        if (empty($this->query)) {
            $this->buildQuery();
        }

        return $this->query;
    }
}
