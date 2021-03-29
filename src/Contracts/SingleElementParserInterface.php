<?php

namespace Grixu\Synchronizer\Contracts;

use Illuminate\Database\Eloquent\Model;
use Spatie\DataTransferObject\DataTransferObject;

interface SingleElementParserInterface
{
    public function parseElement(Model $model): DataTransferObject;
}
