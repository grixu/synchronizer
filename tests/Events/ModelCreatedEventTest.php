<?php

namespace Grixu\Synchronizer\Tests\Events;

use Grixu\SociusModels\Product\Models\Product;
use Grixu\Synchronizer\Events\ModelCreatedEvent;
use Illuminate\Support\Facades\Event;
use Orchestra\Testbench\TestCase;

class ModelCreatedEventTest extends TestCase
{
    /** @test */
    public function it_firing()
    {
        Event::fake();

        event(new ModelCreatedEvent(Product::class));

        Event::assertDispatched(ModelCreatedEvent::class, function ($event) {
            return $event->model == Product::class;
        });
    }
}
