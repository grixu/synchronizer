<?php

namespace Grixu\Synchronizer\Process\Contracts;

interface SyncHandlerInterface
{
    public static function sync(array $data, string $batchId): void;
}
