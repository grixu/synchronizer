<?php

namespace Grixu\Synchronizer\Config;

use Closure;
use Exception;
use Grixu\Synchronizer\Contracts\LoaderInterface;
use Grixu\Synchronizer\Contracts\ParserInterface;
use Grixu\Synchronizer\Traits\CheckClassImplementsInterface;
use Illuminate\Queue\SerializableClosure;

class SyncConfig
{
    use CheckClassImplementsInterface;

    private $currentJob = 0;

    public function __construct(
        protected string $loaderClass,
        protected string $parserClass,
        protected string $localModel,
        protected string $foreignKey,
        protected array $jobsConfig,
        protected ?array $idsToSync = [],
        protected Closure|SerializableClosure|null $syncClosure = null,
        protected Closure|SerializableClosure|null $errorHandler = null
    ) {
        $this->checkClassIsImplementingInterface($loaderClass, LoaderInterface::class);
        $this->checkClassIsImplementingInterface($parserClass, ParserInterface::class);
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

    public function getCurrentJob(): string
    {
        return $this->jobsConfig[$this->currentJob];
    }

    public function getNextJob(): string
    {
        if (count($this->jobsConfig) > $this->currentJob+1) {
            $this->currentJob++;
        }

        return $this->getCurrentJob();
    }

    public function setCurrentJob(int $currentJob): void
    {
        if ($currentJob < 0) {
            throw new Exception('Value is too low');
        }

        if($currentJob > count($this->jobsConfig)-1) {
            throw new Exception('Value is too high');
        }

        $this->currentJob = $currentJob;
    }

    public function getSyncClosure(): Closure|SerializableClosure|null
    {
        return $this->syncClosure;
    }

    public function setSyncClosure(Closure|SerializableClosure|null $syncClosure): void
    {
        $this->syncClosure = $syncClosure;
    }

    public function getErrorHandler(): Closure|SerializableClosure|null
    {
        return $this->errorHandler;
    }

    public function setErrorHandler(Closure|SerializableClosure|null $errorHandler): void
    {
        $this->errorHandler = $errorHandler;
    }
}
