<?php

namespace Grixu\Synchronizer\Actions;

use Grixu\Synchronizer\Config\SyncConfig;
use Grixu\Synchronizer\Events\CollectionSynchronizedEvent;
use Grixu\Synchronizer\Jobs\LoadDataToSyncJob;
use Illuminate\Bus\Batch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;

class StartSyncAction
{
    public function execute(SyncConfig|array $config, string $queue = 'default'): Batch
    {
        $configCollection = $this->prepareConfig($config);
        $jobs = $this->prepareJobs($configCollection);

        return Bus::batch($jobs)
            ->allowFailures()
            ->then(function (Batch $batch) use($configCollection) {
                foreach ($configCollection as $config) {
                    /** @var SyncConfig $config */
                    event(new CollectionSynchronizedEvent($config->getLocalModel()));
                }
            })
            ->catch(function (Batch $batch, \Throwable $exception) use ($configCollection) {
                $configCollection->each(function ($config) use ($exception) {
                    if ($config->getErrorHandler() != null)
                        $config->getErrorHandler()($exception);
                });
            })
            ->onQueue($queue)
            ->dispatch();
    }

    protected function prepareConfig(SyncConfig|array $config): Collection
    {
        if (is_array($config)) {
            $configCollection = collect($config);
            $configCollection = $configCollection->map(fn ($item) => SyncConfig::make(...$item));
        } else {
            $configCollection = collect([$config]);
        }

        return $configCollection;
    }

    protected function prepareJobs(Collection $configCollection): array
    {
        $jobs = [];

        foreach ($configCollection as $config) {
            $jobs[] = new LoadDataToSyncJob($config);
        }

        return $jobs;
    }
}
