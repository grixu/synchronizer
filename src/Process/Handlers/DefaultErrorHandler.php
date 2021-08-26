<?php

namespace Grixu\Synchronizer\Process\Handlers;

use Grixu\Synchronizer\Process\Contracts\ErrorHandlerInterface;
use Illuminate\Support\Facades\Log;

class DefaultErrorHandler implements ErrorHandlerInterface
{
    public static function handle(\Throwable $e): void
    {
        Log::critical($e->getMessage());
    }
}
