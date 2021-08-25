<?php

namespace Grixu\Synchronizer\Config;

use Closure;
use Exception;
use Grixu\Synchronizer\Config\Contracts\SyncConfig as SyncConfigInterface;
use Grixu\Synchronizer\Process\Contracts\LoaderInterface;
use Grixu\Synchronizer\Process\Contracts\ParserInterface;
use Grixu\Synchronizer\Config\Traits\CheckClassImplementsInterface;
use Illuminate\Queue\SerializableClosure;

class SyncConfig implements SyncConfigInterface
{
    use CheckClassImplementsInterface;

    private int $currentJob = 0;

    private static SyncConfigInterface|null $instance = null;

    public function __construct(
        protected string $loaderClass,
        protected string $parserClass,
        protected string $localModel,
        protected string $foreignKey,
        protected array $jobsConfig,
        protected string|null $checksumField = null,
        protected array $timestamps = [],
        protected array $ids = [],
        protected Closure|SerializableClosure|null $syncClosure = null,
        protected Closure|SerializableClosure|null $errorHandler = null
    ) {
        $this->validateChecksum();
        $this->checkClassIsImplementingInterface($loaderClass, LoaderInterface::class);
        $this->checkClassIsImplementingInterface($parserClass, ParserInterface::class);
    }

    protected function validateChecksum(): void
    {
        if (!config('synchronizer.checksum.control')) {
            $this->checksumField = null;
            $this->timestamps = [];
        }
    }

    public static function getInstance(): SyncConfigInterface
    {
        if (empty(static::$instance)) {
            static::$instance = new NullSyncConfig();
        }

        return static::$instance;
    }

    public static function setInstance(SyncConfigInterface $instance)
    {
        static::$instance = $instance;
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

    public function getTimestamps(): array
    {
        return $this->timestamps;
    }

    public function getIds(): array
    {
        return $this->ids;
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

        if ($currentJob > count($this->jobsConfig)-1) {
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
        if ($syncClosure::class === Closure::class) {
            $syncClosure = new SerializableClosure($syncClosure);
        }

        $this->syncClosure = $syncClosure;
    }

    public function getErrorHandler(): Closure|SerializableClosure|null
    {
        return $this->errorHandler;
    }

    public function setErrorHandler(Closure|SerializableClosure|null $errorHandler): void
    {
        if ($errorHandler::class === Closure::class) {
            $errorHandler = new SerializableClosure($errorHandler);
        }

        $this->errorHandler = $errorHandler;
    }

    public function getChecksumField(): string | null
    {
        return $this->checksumField;
    }
}
