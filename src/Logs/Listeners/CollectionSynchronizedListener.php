<?php

namespace Grixu\Synchronizer\Logs\Listeners;

use Grixu\Synchronizer\Events\CollectionSynchronizedEvent;
use Grixu\Synchronizer\Logs\Logger;

class CollectionSynchronizedListener
{
    public function handle(CollectionSynchronizedEvent $event)
    {
        $logger = new Logger($event->batchId, $event->model);
        $logger->report();
    }
}
