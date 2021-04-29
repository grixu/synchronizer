<?php

namespace Grixu\Synchronizer\Tests\Jobs;

use Grixu\Synchronizer\Config\SyncConfig;
use Grixu\Synchronizer\Jobs\LoadAllAndParseJob;
use Grixu\Synchronizer\Tests\Helpers\FakeCancelJob;
use Grixu\Synchronizer\Tests\Helpers\FakeSyncConfig;
use Grixu\Synchronizer\Tests\Helpers\SyncTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;

class LoadAllAndParseJobTest extends SyncTestCase
{
    use DatabaseMigrations;

    protected SyncConfig $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = FakeSyncConfig::makeLoadAllAndParse();
    }

    /** @test */
    public function it_constructs()
    {
        $obj = new LoadAllAndParseJob($this->config);

        $this->assertEquals(LoadAllAndParseJob::class, $obj::class);
    }

    /** @test */
    public function it_run_in_queue()
    {
        Queue::fake();

        dispatch(new LoadAllAndParseJob($this->config));

        Queue::assertPushed(LoadAllAndParseJob::class, 1);
    }

    /** @test */
    public function it_run_in_batch()
    {
        Queue::fake();
        $bus = Bus::fake();

        $job = new LoadAllAndParseJob($this->config);
        $batch = $bus->batch(
            [
                $job
            ]
        );

        $batch->dispatch();

        $bus->assertBatched(
            function ($batch) {
                return $batch->jobs->count() > 0;
            }
        );
    }

    /** @test */
    public function it_start_parsing_job()
    {
        $obj = new LoadAllAndParseJob($this->config);
        $batch = Bus::batch(
            [
                $obj
            ]
        )
            ->allowFailures()
            ->dispatch();

        $this->assertGreaterThan(1, $batch->totalJobs);
    }

    /** @test */
    public function it_reacts_on_batch_cancellation()
    {
        Queue::fake();
        $bus = Bus::fake();

        $job = new LoadAllAndParseJob($this->config);
        $batch = $bus->batch(
            [
                $job
            ]
        );

        $batch->dispatch()->cancel();

        $bus->assertBatched(
            function ($batch) {
                return $batch->jobs->count() > 0;
            }
        );
    }

    /** @test */
    public function it_reacts_to_cancellation()
    {
        $job = new LoadAllAndParseJob($this->config);
        $batch = Bus::batch(
            [
                (new FakeCancelJob()),
                $job->delay(1000)
            ]
        );

        $finishedBatch = $batch->dispatch();

        $this->assertTrue($finishedBatch->cancelled());
    }
}
