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

class LoadDataToSyncJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public SyncConfig $config)
    {
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

        if ($this->batch()) {
            $jobs = [];

            foreach ($dataCollection as $data) {
                $jobs[] = new ParseLoadedDataJob($data, $this->config);
            }

            $this->batch()->add($jobs);
        }
    }
}
