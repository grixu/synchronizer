<?php

namespace Grixu\Synchronizer\Config;

use Closure;
use Exception;
use Grixu\Synchronizer\Config\Contracts\ProcessConfigInterface;
use Grixu\Synchronizer\Config\Exceptions\EmptyHandlersConfiguration;
use Grixu\Synchronizer\Config\Exceptions\EmptyJobsConfiguration;
use Grixu\Synchronizer\Config\Helpers\CheckInterface;
use Grixu\Synchronizer\Process\Contracts\ErrorHandlerInterface;
use Grixu\Synchronizer\Process\Contracts\SyncHandlerInterface;

class ProcessConfig implements ProcessConfigInterface
{
    private int $currentJob = 0;

    public static function make(
        string $loaderClass,
        string $parserClass,
        array|string|null $jobsConfig = null,
        string $syncHandler = null,
        string $errorHandler = null
    ): ProcessConfig {
        $defaultHandlers = config('synchronizer.handlers');

        if (empty($defaultHandlers['sync']) || empty($defaultHandlers['error'])) {
            throw new EmptyHandlersConfiguration();
        }

        if (empty($syncHandler)) {
            $syncHandler = $defaultHandlers['sync'];
        }
        if (empty($errorHandler)) {
            $errorHandler = $defaultHandlers['error'];
        }

        $checkInterface = app(CheckInterface::class);
        $checkInterface($syncHandler, SyncHandlerInterface::class);
        $checkInterface($errorHandler, ErrorHandlerInterface::class);

        if (!empty($jobsConfig) && is_string($jobsConfig)) {
            $jobsConfig = config('synchronizer.jobs.' . $jobsConfig);
        }

        if (empty($jobsConfig)) {
            $jobsConfig = config('synchronizer.jobs.default');
        }

        if (empty($jobsConfig)) {
            throw new EmptyJobsConfiguration();
        }

        return new ProcessConfig(
            loaderClass: $loaderClass,
            parserClass: $parserClass,
            jobsConfig: $jobsConfig,
            syncHandler: $syncHandler,
            errorHandler: $errorHandler
        );
    }

    private function __construct(
        protected string $loaderClass,
        protected string $parserClass,
        protected array $jobsConfig,
        protected string $syncHandler,
        protected string $errorHandler
    ) {
    }

    public function getLoaderClass(): string
    {
        return $this->loaderClass;
    }

    public function getParserClass(): string
    {
        return $this->parserClass;
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

    public function getSyncHandler(): string
    {
        return $this->syncHandler;
    }

    public function setSyncHandler(string $syncHandler): void
    {
        $checkInterface = app(CheckInterface::class);
        $checkInterface($syncHandler, SyncHandlerInterface::class);

        $this->syncHandler = $syncHandler;
    }

    public function getErrorHandler(): string
    {
        return $this->errorHandler;
    }

    public function setErrorHandler(string $errorHandler): void
    {
        $checkInterface = app(CheckInterface::class);
        $checkInterface($errorHandler, ErrorHandlerInterface::class);

        $this->errorHandler = $errorHandler;
    }
}
