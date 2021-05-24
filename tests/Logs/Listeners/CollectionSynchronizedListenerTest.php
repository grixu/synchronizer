<?php

namespace Grixu\Synchronizer\Tests\Logs\Listeners;

use Grixu\SociusModels\Product\Models\Product;
use Grixu\Synchronizer\Process\Events\CollectionSynchronizedEvent;
use Grixu\Synchronizer\Logs\Listeners\CollectionSynchronizedListener;
use Grixu\Synchronizer\Logs\Logger;
use Grixu\Synchronizer\Logs\Models\Log;
use Grixu\Synchronizer\Logs\Notifications\LoggerNotification;
use Grixu\Synchronizer\Process\Jobs\LoadDataToSyncJob;
use Grixu\Synchronizer\Tests\Helpers\FakeSyncConfig;
use Grixu\Synchronizer\Tests\Helpers\SyncTestCase;
use Grixu\Synchronizer\Tests\Helpers\TestCase;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class CollectionSynchronizedListenerTest extends SyncTestCase
{
    protected Batch $batch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->batch = Bus::batch(
            [
                new LoadDataToSyncJob(FakeSyncConfig::make()),
            ]
        )->dispatch();
        $this->createLog($this->batch->id);
    }

    protected function createLog($batchId): void
    {
        Log::create(
            [
                'batch_id' => $batchId,
                'model' => Product::class,
                'changed' => 1,
                'log' => [],
                'type' => Logger::MODEL
            ]
        );
    }

    /** @test */
    public function it_trigger_sending_notification()
    {
        Notification::fake();

        DB::table('job_batches')->where('id', $this->batch->id)->update(['finished_at' => now()->subDay()]);

        $obj = new CollectionSynchronizedListener();
        $obj->handle(new CollectionSynchronizedEvent(Product::class, $this->batch->id));

        Notification::assertTimesSent(1, LoggerNotification::class);
    }

    /** @test */
    public function it_waits_minute_after_finish_job()
    {
        Notification::fake();

        $obj = new CollectionSynchronizedListener();
        $obj->handle(new CollectionSynchronizedEvent(Product::class, $this->batch->id));

        Notification::assertTimesSent(0, LoggerNotification::class);
    }

    /** @test */
    public function it_releases_if_batch_is_not_finished()
    {
        Notification::fake();
        $this->batch = Bus::batch([])->dispatch();
        $this->createLog($this->batch->id);

        $obj = new CollectionSynchronizedListener();
        $obj->handle(new CollectionSynchronizedEvent(Product::class, $this->batch->id));

        Notification::assertTimesSent(0, LoggerNotification::class);
    }

    /** @test */
    public function it_triggers_through_event_provider()
    {
        Notification::fake();
        DB::table('job_batches')->where('id', $this->batch->id)->update(['finished_at' => now()->subDay()]);

        event(new CollectionSynchronizedEvent(Product::class, $this->batch->id));

        Notification::assertTimesSent(1, LoggerNotification::class);
    }
}
