<?php

namespace Grixu\Synchronizer\Process\Jobs;

use Grixu\Synchronizer\Engine\Config\EngineConfig;
use Grixu\Synchronizer\Engine\Contracts\EngineConfigInterface;
use Grixu\Synchronizer\Process\Contracts\LoaderInterface;
use Grixu\Synchronizer\Process\Contracts\ProcessConfigInterface;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class LoadDataToSyncJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public $timeout = 600;
    public $tries = 0;
    public $maxExceptions = 2;

    public function __construct(
        public ProcessConfigInterface $processConfig,
        public EngineConfigInterface $engineConfig
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

        $loaderClass = $this->processConfig->getLoaderClass();
        /** @var LoaderInterface $loader */
        $loader = app($loaderClass);

        $loader->buildQuery($this->engineConfig->getIds());
        $dataCollection = $loader->get();

        if ($this->batch()) {
            $jobs = [];
            $jobClass = $this->processConfig->getNextJob();

            /** @var Collection $data */
            foreach ($dataCollection as $data) {
                $jobs[] = new $jobClass($this->processConfig, $this->engineConfig, $data);
            }

            $this->batch()->add($jobs);
        }
    }
}
