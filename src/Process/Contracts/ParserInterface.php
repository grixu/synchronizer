<?php

namespace Grixu\Synchronizer\Process\Contracts;

use Illuminate\Support\Collection;

interface ParserInterface
{
    public function parse(Collection $collection): Collection;
}
