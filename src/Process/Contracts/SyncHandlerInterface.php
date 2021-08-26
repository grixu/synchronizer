<?php

namespace Grixu\Synchronizer\Process\Contracts;

interface SyncHandlerInterface
{
    public function sync(array $data, string|null $batchId): void;
}
