<?php

namespace Grixu\Synchronizer\Jobs;

use Grixu\Synchronizer\Config\SyncConfig;
use Grixu\Synchronizer\CollectionSynchronizer;
use Grixu\Synchronizer\Exceptions\EmptyForeignKeyInDto;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use TypeError;

class SyncDataParsedJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public Collection $dtoCollection, public SyncConfig $config)
    {
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
        } catch (EmptyForeignKeyInDto) {
            return;
        }

        try {
            $synchronizer->sync();
        } catch (TypeError $e) {
            if ($this->config->getErrorHandler() !== null)
                $this->config->getErrorHandler()($e);

            return;
        }
    }
}
