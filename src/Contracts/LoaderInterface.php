<?php

namespace Grixu\Synchronizer\Contracts;

use Illuminate\Support\Collection;

interface LoaderInterface
{
    public function buildQuery(?array $foreignKeys = []): static;
    public function get(): Collection;
    public function getRaw(): Collection;
    public function getCount(): int;
}
