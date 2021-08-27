<?php

namespace Grixu\Synchronizer\Process\Jobs;

use Grixu\Synchronizer\Config\Contracts\ProcessConfigInterface;
use Grixu\Synchronizer\Engine\Config\EngineConfig;
use Grixu\Synchronizer\Engine\Contracts\EngineConfigInterface;
use Grixu\Synchronizer\Process\Contracts\ErrorHandlerInterface;
use Grixu\Synchronizer\Process\Contracts\SyncHandlerInterface;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Carbon;
use Throwable;

class SyncParsedDataJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public $timeout = 600;
    public $tries = 0;
    public $maxExceptions = 4;

    public function __construct(
        public ProcessConfigInterface $processConfig,
        public EngineConfigInterface $engineConfig,
        public array $data,
    ) {
    }

    public function backoff(): int
    {
        return 60 * $this->attempts();
    }

    public function retryUntil(): Carbon
    {
        return now()->addHour();
    }

    public function handle()
    {
        if (optional($this->batch())->cancelled()) {
            return;
        }

        EngineConfig::setInstance($this->engineConfig);

        try {
            /** @var SyncHandlerInterface $handler */
            $handler = app($this->processConfig->getSyncHandler());
            $handler->sync($this->data, $this->batchId);
        } catch (Throwable $e) {
            /** @var ErrorHandlerInterface $errorHandler */
            $errorHandler = app($this->processConfig->getErrorHandler());
            $errorHandler->handle($e);

            return;
        }
    }
}
