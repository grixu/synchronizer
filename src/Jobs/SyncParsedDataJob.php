<?php

namespace Grixu\Synchronizer\Jobs;

use Grixu\Synchronizer\Config\SyncConfig;
use Grixu\Synchronizer\CollectionSynchronizer;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
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

    public function __construct(public Collection $dtoCollection, public SyncConfig $config)
    {
    }

    public function backoff(): int
    {
        return 30 * $this->attempts();
    }

    public function retryUntil(): Carbon
    {
        return now()->addSeconds(
            $this->timeout * $this->maxExceptions + $this->backoff()
        );
    }

    public function handle()
    {
        if (optional($this->batch())->cancelled()) {
            return;
        }

        $closure = $this->config->getSyncClosure();


        if ($closure) {
            $closure($this->dtoCollection, $this->config);
        } else {
            $this->defaultSyncHandler();
        }
    }

    protected function defaultSyncHandler()
    {
        try {
            $synchronizer = new CollectionSynchronizer(
                $this->dtoCollection,
                $this->config->getLocalModel(),
                $this->config->getForeignKey(),
                $this->config->getErrorHandler(),
            );

            $synchronizer->sync();
        } catch (Throwable $e) {
            if ($this->config->getErrorHandler() !== null)
                $this->config->getErrorHandler()($e);

            return;
        }
    }
}
