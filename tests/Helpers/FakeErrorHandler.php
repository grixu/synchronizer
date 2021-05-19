<?php

namespace Grixu\Synchronizer\Tests\Helpers;

use Grixu\Synchronizer\Process\Contracts\ErrorHandlerInterface;
use Illuminate\Queue\SerializableClosure;
use JetBrains\PhpStorm\Pure;

class FakeErrorHandler implements ErrorHandlerInterface
{
    #[Pure]
    public static function make(): SerializableClosure
    {
        return new SerializableClosure(function ($e) {});
    }
}
