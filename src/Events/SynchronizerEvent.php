<?php

namespace Grixu\Synchronizer\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class SynchronizerEvent
{
    use Dispatchable;
    use InteractsWithSockets;

    public function __construct(public string $model, public array $changed)
    {}
}
