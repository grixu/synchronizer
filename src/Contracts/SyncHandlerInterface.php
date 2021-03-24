<?php

namespace Grixu\Synchronizer\Contracts;

use Illuminate\Queue\SerializableClosure;

interface SyncHandlerInterface
{
    public static function make(): SerializableClosure;
}
