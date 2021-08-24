<?php

namespace Grixu\Synchronizer\Config\Contracts;

use Closure;
use Illuminate\Queue\SerializableClosure;

interface SyncConfig
{
    public function getLoaderClass(): string;
    public function getParserClass(): string;
    public function getLocalModel(): string;
    public function getForeignKey(): string;
    public function getTimestamps(): array;
    public function getIds(): array;
    public function getCurrentJob(): string;
    public function getNextJob(): string;
    public function setCurrentJob(int $currentJob): void;
    public function getSyncClosure(): Closure|SerializableClosure|null;
    public function setSyncClosure(Closure|SerializableClosure|null $syncClosure): void;
    public function getErrorHandler(): Closure|SerializableClosure|null;
    public function setErrorHandler(Closure|SerializableClosure|null $errorHandler): void;
    public function getChecksumField(): string | null;
}
