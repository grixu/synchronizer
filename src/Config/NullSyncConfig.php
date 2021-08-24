<?php

namespace Grixu\Synchronizer\Config;

use Closure;
use Grixu\Synchronizer\Config\Contracts\SyncConfig as SyncConfigInterface;
use Illuminate\Queue\SerializableClosure;

class NullSyncConfig implements SyncConfigInterface
{
    public function getLoaderClass(): string
    {
        return '';
    }

    public function getParserClass(): string
    {
        return '';
    }

    public function getLocalModel(): string
    {
        return '';
    }

    public function getForeignKey(): string
    {
        return '';
    }

    public function getTimestamps(): array
    {
        return [];
    }

    public function getIds(): array
    {
        return [];
    }

    public function getCurrentJob(): string
    {
        return '';
    }

    public function getNextJob(): string
    {
        return '';
    }

    public function setCurrentJob(int $currentJob): void
    {
    }

    public function getSyncClosure(): Closure|SerializableClosure|null
    {
        return function() {};
    }

    public function setSyncClosure(SerializableClosure|Closure|null $syncClosure): void
    {
    }

    public function getErrorHandler(): Closure|SerializableClosure|null
    {
        return function() {};
    }

    public function setErrorHandler(SerializableClosure|Closure|null $errorHandler): void
    {
    }

    public function getChecksumField(): string|null
    {
        return null;
    }
}
