<?php

namespace Grixu\Synchronizer\Process\Contracts;

use Spatie\LaravelData\Data;

interface SingleElementParserInterface
{
    public function parseElement($model): Data;
}
