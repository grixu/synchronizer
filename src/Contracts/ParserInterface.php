<?php

namespace Grixu\Synchronizer\Contracts;

use Illuminate\Support\Collection;

interface ParserInterface
{
    public function parse(Collection $collection): Collection;
}
