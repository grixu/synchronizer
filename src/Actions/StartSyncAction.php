<?php

namespace Grixu\Synchronizer\Actions;

use Exception;
use Grixu\Synchronizer\Config\SyncConfig;
use Grixu\Synchronizer\Config\SyncConfigFactory;
use Grixu\Synchronizer\Events\CollectionSynchronizedEvent;
use Grixu\Synchronizer\Jobs\LoadDataToSyncJob;
use Illuminate\Bus\Batch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;

class StartSyncAction
{
    public function execute(SyncConfig|array $config, string $queue = 'default'): Batch
    {
        $this->checkConfig();
        $configCollection = $this->prepareConfig($config);
        $jobs = $this->prepareJobs($configCollection);

        return Bus::batch($jobs)
            ->allowFailures()
            // @codeCoverageIgnoreStart
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
            // @codeCoverageIgnoreEnd
            ->onQueue($queue)
            ->dispatch();
    }

    protected function checkConfig(): void
    {
        if (empty(config('synchronizer.jobs.load'))) {
            throw new Exception('Empty configuration: no LoadingJob configured');
        }
    }

    protected function prepareConfig(SyncConfig|array $config): Collection
    {
        if (is_array($config)) {
            $configCollection = collect($config);
            $configCollection = $configCollection->map(function ($item) {
                if (is_array($item)) {
                    /** @var SyncConfigFactory $factory */
                    $factory = app(SyncConfigFactory::class);
                    return $factory->make(...$item);
                }

                return $item;
            });
        } else {
            $configCollection = collect([$config]);
        }

        return $configCollection;
    }

    protected function prepareJobs(Collection $configCollection): array
    {
        $jobs = [];
        $jobClass = config('synchronizer.jobs.load');

        foreach ($configCollection as $config) {
            $jobs[] = new $jobClass($config);
        }

        return $jobs;
    }
}
