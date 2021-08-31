<?php

namespace Grixu\Synchronizer\Process\Contracts;

interface ErrorHandlerInterface
{
    public function handle(\Throwable $e): void;
}
