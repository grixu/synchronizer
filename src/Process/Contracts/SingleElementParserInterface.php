<?php

namespace Grixu\Synchronizer\Process\Contracts;

use Spatie\DataTransferObject\DataTransferObject;

interface SingleElementParserInterface
{
    public function parseElement($model): DataTransferObject;
}
