<?php

namespace Grixu\Synchronizer\Process\Contracts;

use Illuminate\Queue\SerializableClosure;

interface ErrorHandlerInterface
{
    public static function make(): SerializableClosure;
}
