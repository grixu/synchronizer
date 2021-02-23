<?php

namespace Grixu\Synchronizer\Tests\Events;

use Grixu\SociusModels\Product\Models\Product;
use Grixu\Synchronizer\Events\ModelSynchronizedEvent;
use Illuminate\Support\Facades\Event;
use Orchestra\Testbench\TestCase;

class ModelSynchronizedEventTest extends TestCase
{
    /** @test */
    public function it_firing()
    {
        Event::fake();

        event(new ModelSynchronizedEvent(Product::class));

        Event::assertDispatched(ModelSynchronizedEvent::class, function ($event) {
            return $event->model == Product::class;
        });
    }
}
