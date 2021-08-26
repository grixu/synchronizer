<?php

namespace Grixu\Synchronizer\Process\Contracts;

interface ErrorHandlerInterface
{
    public static function handle(\Throwable $e): void;
}
