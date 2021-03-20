<?php

namespace Grixu\Synchronizer\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CollectionSynchronizedEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public string $model;

    public function __construct(string $model)
    {
        $this->model = $model;
    }
}
