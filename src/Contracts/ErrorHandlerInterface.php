<?php

namespace Grixu\Synchronizer\Contracts;

use Illuminate\Queue\SerializableClosure;

interface ErrorHandlerInterface
{
    public static function make(): SerializableClosure;
}
