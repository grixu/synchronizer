<?php

namespace Grixu\Synchronizer\Process\Jobs;

use Grixu\Synchronizer\Config\SyncConfig;
use Grixu\Synchronizer\Synchronizer;
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

    public function __construct(public array $data, public SyncConfig $config)
    {
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

        $closure = $this->config->getSyncClosure();

        if ($closure) {
            $closure($this->data, $this->config);
        } else {
            $this->defaultSyncHandler();
        }
    }

    protected function defaultSyncHandler()
    {
        try {
            $synchronizer = new Synchronizer(
                $this->data,
                $this->config,
                $this->batchId
            );

            $synchronizer->sync();
        } catch (Throwable $e) {
            ray($e);
            if ($this->config->getErrorHandler() !== null) {
                $this->config->getErrorHandler()($e);
            }

            return;
        }
    }
}
