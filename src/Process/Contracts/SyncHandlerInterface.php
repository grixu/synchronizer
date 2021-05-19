<?php

namespace Grixu\Synchronizer\Process\Contracts;

use Illuminate\Queue\SerializableClosure;

interface SyncHandlerInterface
{
    public static function make(): SerializableClosure;
}
