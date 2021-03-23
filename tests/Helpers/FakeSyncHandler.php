<?php

namespace Grixu\Synchronizer\Tests\Helpers;

use Grixu\Synchronizer\Contracts\SyncHandlerInterface;
use Illuminate\Queue\SerializableClosure;
use JetBrains\PhpStorm\Pure;

class FakeSyncHandler implements SyncHandlerInterface
{
    #[Pure]
    public static function make(): SerializableClosure
    {
        return new SerializableClosure(function ($collection, $config) {});
    }
}
