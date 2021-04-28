<?php

namespace Grixu\Synchronizer\Jobs;

use Grixu\Synchronizer\Config\SyncConfig;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Grixu\Synchronizer\Contracts\LoaderInterface;
use Illuminate\Support\Carbon;

class LoadDataToSyncJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public $timeout = 60;
    public $tries = 3;
    public $maxExceptions = 3;

    public function __construct(public SyncConfig $config)
    {
    }

    public function backoff(): int
    {
        return 60 * $this->attempts();
    }

    public function retryUntil(): Carbon
    {
        return now()
            ->addSeconds(
                $this->timeout * $this->tries * $this->maxExceptions
            );
    }

    public function handle()
    {
        if (optional($this->batch())->cancelled()) {
            return;
        }

        $loaderClass = $this->config->getLoaderClass();
        /** @var LoaderInterface $loader */
        $loader = app($loaderClass);

        $loader->buildQuery($this->config->getIdsToSync());
        $dataCollection = $loader->get();
        ray($dataCollection);

        if ($this->batch()) {
            $jobs = [];
            $jobClass = $this->config->getNextJob();

            foreach ($dataCollection as $data) {
                $jobs[] = new $jobClass($data, $this->config);
            }

            ray($jobs);
            $this->batch()->add($jobs);
        }
    }
}
