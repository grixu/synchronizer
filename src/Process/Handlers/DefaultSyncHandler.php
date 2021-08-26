<?php

namespace Grixu\Synchronizer\Process\Handlers;

use Grixu\Synchronizer\Process\Contracts\SyncHandlerInterface;
use Grixu\Synchronizer\Synchronizer;

class DefaultSyncHandler implements SyncHandlerInterface
{
    public static function sync(array $data, string $batchId): void
    {
        $synchronizer = new Synchronizer(
            $data,
            $batchId
        );

        $synchronizer->sync();
    }
}
