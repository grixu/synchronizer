<?php

namespace Grixu\Synchronizer\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class CollectionSynchronizedEvent
{
    use Dispatchable;
    use InteractsWithSockets;

    public function __construct(public string $model, public string $batchId)
    {
    }
}
