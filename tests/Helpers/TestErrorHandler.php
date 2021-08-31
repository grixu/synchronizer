<?php

namespace Grixu\Synchronizer\Tests\Helpers;

use Grixu\Synchronizer\Process\Contracts\ErrorHandlerInterface;
use Illuminate\Support\Facades\Http;

class TestErrorHandler implements ErrorHandlerInterface
{
    public function handle(\Throwable $e): void
    {
        Http::get('http://testable.dev');
    }
}
