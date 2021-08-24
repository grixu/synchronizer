<?php

namespace Grixu\Synchronizer\Process\Jobs;

use Grixu\Synchronizer\Config\SyncConfig;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Grixu\Synchronizer\Process\Contracts\LoaderInterface;
use Illuminate\Support\Carbon;

class LoadDataToSyncJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public $timeout = 600;
    public $tries = 0;
    public $maxExceptions = 2;

    public function __construct(public SyncConfig $config)
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

        SyncConfig::setInstance($this->config);

        $loaderClass = $this->config->getLoaderClass();
        /** @var LoaderInterface $loader */
        $loader = app($loaderClass);

        $loader->buildQuery($this->config->getIds());
        $dataCollection = $loader->get();

        if ($this->batch()) {
            $jobs = [];
            $jobClass = $this->config->getNextJob();

            foreach ($dataCollection as $data) {
                $jobs[] = new $jobClass($data, $this->config);
            }

            $this->batch()->add($jobs);
        }
    }
}
