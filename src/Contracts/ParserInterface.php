<?php

namespace Grixu\Synchronizer\Contracts;

use Illuminate\Database\Eloquent\Model;
use Spatie\DataTransferObject\DataTransferObject;

interface ParserInterface
{
    public function parse(Model $model): DataTransferObject;
}
