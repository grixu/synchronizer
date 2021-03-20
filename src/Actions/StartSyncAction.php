<?php

namespace Grixu\Synchronizer\Actions;

use Grixu\Synchronizer\Config\SyncConfig;
use Grixu\Synchronizer\Events\CollectionSynchronizedEvent;
use Grixu\Synchronizer\Jobs\LoadDataToSyncJob;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;

class StartSyncAction
{
    public function execute(array $config, string $queue = 'default'): Batch
    {
        $configCollection = collect($config);
        $configCollection = $configCollection->map(fn ($item) => SyncConfig::make(...$item));

        $jobs = [];

        foreach ($configCollection as $config) {
            $jobs[] = new LoadDataToSyncJob($config);
        }

        return Bus::batch($jobs)
            ->allowFailures()
            ->then(function (Batch $batch) use($configCollection) {
                foreach ($configCollection as $config) {
                    /** @var SyncConfig $config */
                    event(new CollectionSynchronizedEvent($config->getLocalModel()));
                }
            })
            ->catch(function (Batch $batch, \Throwable $exception) use ($configCollection) {
                $configCollection->each(fn ($config) => $config->getErrorHandler());
            })
            ->onQueue($queue)
            ->dispatch();
    }
}
