<?php

namespace Grixu\Synchronizer\Logs\Listeners;

use Grixu\Synchronizer\Process\Events\CollectionSynchronizedEvent;
use Grixu\Synchronizer\Logs\Logger;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Bus;

class CollectionSynchronizedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public $delay = 60;
    public $tries = 2;
    public $timeout = 30;
    public $backoff = [5, 20];

    public function retryUntil()
    {
        return now()->addMinutes(5);
    }

    public function viaQueue()
    {
        return config('synchronizer.queues.notifications');
    }

    public function handle(CollectionSynchronizedEvent $event)
    {
        $batch = Bus::findBatch($event->batchId);

        if(!$batch->finished()) {
            return $this->release(60);
        }

        if (!now()->isAfter($batch->finishedAt->addMinute())) {
            return $this->release(60);
        }

        $logger = new Logger($event->batchId, $event->model);
        $logger->report();
    }
}
