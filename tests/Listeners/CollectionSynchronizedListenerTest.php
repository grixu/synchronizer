<?php

namespace Grixu\Synchronizer\Tests\Listeners;

use Grixu\SociusModels\Product\Models\Product;
use Grixu\Synchronizer\Events\CollectionSynchronizedEvent;
use Grixu\Synchronizer\Listeners\CollectionSynchronizedListener;
use Grixu\Synchronizer\Logger;
use Grixu\Synchronizer\Models\Log;
use Grixu\Synchronizer\Notifications\LoggerNotification;
use Grixu\Synchronizer\Tests\Helpers\TestCase;
use Illuminate\Support\Facades\Notification;

class CollectionSynchronizedListenerTest extends TestCase
{
    /** @test */
    public function it_trigger_sending_notification()
    {
        Notification::fake();

        $this->createLog();

        $obj = new CollectionSynchronizedListener();
        $obj->handle(new CollectionSynchronizedEvent(Product::class, 'none'));

        Notification::assertTimesSent(1, LoggerNotification::class);
    }

    protected function createLog(): void
    {
        Log::create(
            [
                'batch_id' => 'none',
                'model' => Product::class,
                'changed' => 1,
                'log' => [],
                'type' => Logger::MODEL
            ]
        );
    }

    /** @test */
    public function it_triggers_through_event_provider()
    {
        Notification::fake();

        $this->createLog();
        event(new CollectionSynchronizedEvent(Product::class, 'none'));

        Notification::assertTimesSent(1, LoggerNotification::class);
    }
}