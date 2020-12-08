<?php


namespace Grixu\Synchronizer\Events;


use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class SynchronizerDetectChangesEvent
 * @package Grixu\Synchronizer\Events
 */
class SynchronizerDetectChangesEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Model $local;

    public function __construct(Model $local)
    {
        $this->local = $local;
    }
}
