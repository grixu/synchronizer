<?php

namespace Grixu\Synchronizer\Config;

use Closure;
use JetBrains\PhpStorm\Pure;

class SyncConfig
{
    public function __construct(
        protected string $loaderClass,
        protected string $parserClass,
        protected string $localModel,
        protected string $foreignKey,
        protected ?array $idsToSync = [],
        protected ?Closure $syncClosure = null,
        protected ?Closure $errorHandler = null
    ) {
    }

    #[Pure]
    public static function make(
        ...$config
    ): static {
        return new static(...$config);
    }

    public function getLoaderClass(): string
    {
        return $this->loaderClass;
    }

    public function getParserClass(): string
    {
        return $this->parserClass;
    }

    public function getLocalModel(): string
    {
        return $this->localModel;
    }

    public function getForeignKey(): string
    {
        return $this->foreignKey;
    }

    public function getIdsToSync(): ?array
    {
        return $this->idsToSync;
    }

    public function setIdsToSync(?array $idsToSync): void
    {
        $this->idsToSync = $idsToSync;
    }

    public function getSyncClosure(): ?Closure
    {
        return $this->syncClosure;
    }

    public function setSyncClosure(?Closure $syncClosure): void
    {
        $this->syncClosure = $syncClosure;
    }

    public function getErrorHandler(): ?Closure
    {
        return $this->errorHandler;
    }

    public function setErrorHandler(?Closure $errorHandler): void
    {
        $this->errorHandler = $errorHandler;
    }
}
