<?php

namespace Grixu\Synchronizer\Abstracts;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class AbstractSynchronizerEvent
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
