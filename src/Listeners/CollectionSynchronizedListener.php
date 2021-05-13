<?php

namespace Grixu\Synchronizer\Listeners;

use Grixu\Synchronizer\Events\CollectionSynchronizedEvent;
use Grixu\Synchronizer\Logger;

class CollectionSynchronizedListener
{
    public function handle(CollectionSynchronizedEvent $event)
    {
        $logger = new Logger($event->batchId, $event->model);
        $logger->report();
    }
}
