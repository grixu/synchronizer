<?php

namespace Grixu\Synchronizer\Engine\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class SynchronizerEvent
{
    use Dispatchable;
    use InteractsWithSockets;

    public function __construct(public string $model, public string|null $checksumField, public array $changed)
    {
    }
}
