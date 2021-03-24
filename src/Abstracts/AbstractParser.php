<?php

namespace Grixu\Synchronizer\Abstracts;

use Grixu\Synchronizer\Contracts\ParserInterface;
use Grixu\Synchronizer\Contracts\SingleElementParserInterface;
use Illuminate\Support\Collection;

abstract class AbstractParser implements ParserInterface, SingleElementParserInterface
{
    public function parse(Collection $collection): Collection
    {
        return $collection->map(fn($item) => $this->parseElement($item));
    }
}
