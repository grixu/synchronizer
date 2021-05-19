<?php

namespace Grixu\Synchronizer\Process\Contracts;

use Illuminate\Support\Collection;

interface LoaderInterface
{
    public function buildQuery(?array $foreignKeys = []): static;
    public function get(): Collection;
    public function getRaw(): Collection;
    public function getCount(): int;
    public function getBuilder();
}
