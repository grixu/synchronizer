<?php

namespace Grixu\Synchronizer\Process\Contracts;

use Illuminate\Database\Eloquent\Model;
use Spatie\DataTransferObject\DataTransferObject;

interface SingleElementParserInterface
{
    public function parseElement(Model $model): DataTransferObject;
}
