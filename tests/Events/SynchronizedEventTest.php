<?php

namespace Grixu\Synchronizer\Tests\Events;

use Grixu\SociusModels\Product\Models\Product;
use Grixu\Synchronizer\Events\CollectionSynchronizedEvent;
use Grixu\Synchronizer\Tests\Helpers\TestCase;
use Illuminate\Support\Facades\Event;

class SynchronizedEventTest extends TestCase
{
    /** @test */
    public function it_firing()
    {
        Event::fake();

        event(new CollectionSynchronizedEvent(Product::class));

        Event::assertDispatched(CollectionSynchronizedEvent::class, 1);
    }
}
