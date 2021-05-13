<?php

namespace Grixu\Synchronizer\Providers;

use Grixu\Synchronizer\Events\CollectionSynchronizedEvent;
use Grixu\Synchronizer\Listeners\CollectionSynchronizedListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        CollectionSynchronizedEvent::class => [
            CollectionSynchronizedListener::class
        ]
    ];

    public function boot()
    {
        parent::boot();
    }
}
