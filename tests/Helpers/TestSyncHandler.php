<?php

namespace Grixu\Synchronizer\Tests\Helpers;

use Grixu\Synchronizer\Process\Contracts\SyncHandlerInterface;
use Illuminate\Support\Facades\Http;

class TestSyncHandler implements SyncHandlerInterface
{
    public function sync(array $data, string|null $batchId): void
    {
        Http::get('http://testable.dev');
    }
}
