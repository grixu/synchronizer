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
            $this->checkBuildQuery();

            $this->data = $this->query->get();
            $this->count = $this->data->count();
        }
    }

    protected function checkBuildQuery(): void
    {
        if (empty($this->query)) {
            $this->buildQuery();
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

    public function getPiece(int $piece): Collection
    {
        $this->checkBuildQuery();
        $pieceSize = config('synchronizer.sync.default_chunk_size');

        $query = $this->query->limit($pieceSize);

        if ($piece > 1) {
            $query = $query->offset(($piece - 1) * $pieceSize);
        }

        return $query->get();
    }

    public function getBuilder(): Builder
    {
        if (empty($this->query)) {
            $this->buildQuery();
        }

        return $this->query;
    }
}
