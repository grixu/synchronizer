<?php

namespace Grixu\Synchronizer\Process\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class CollectionSynchronizedEvent
{
    use Dispatchable;
    use InteractsWithSockets;

    public function __construct(public string $model, public string|null $checksumField, public string|null $batchId = null)
    {
    }
}
