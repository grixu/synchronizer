<?php

namespace Grixu\Synchronizer\Logs\Listeners;

use Grixu\Synchronizer\Process\Events\CollectionSynchronizedEvent;
use Grixu\Synchronizer\Logs\Logger;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;

class CollectionSynchronizedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public int $delay = 60;
    public int $tries = 2;
    public int $timeout = 30;
    public array $backoff = [5, 20];

    public function retryUntil(): Carbon
    {
        return now()->addMinutes(5);
    }

    public function viaQueue()
    {
        return config('synchronizer.queues.notifications');
    }

    public function handle(CollectionSynchronizedEvent $event): void
    {
        $batch = Bus::findBatch($event->batchId);

        if ($batch) {
            if (!$batch->finished()) {
                $this->release(60);
                return;
            }

            if (!now()->isAfter($batch->finishedAt->addMinute())) {
                $this->release(60);
                return;
            }
        }

        $logger = new Logger($event->batchId, $event->model);
        $logger->report();
    }
}
