<?php

namespace Grixu\Synchronizer\Tests\Process\Jobs;

use Grixu\Synchronizer\Config\SyncConfig;
use Grixu\Synchronizer\Process\Jobs\ChunkLoadAndParseJob;
use Grixu\Synchronizer\Tests\Helpers\FakeCancelJob;
use Grixu\Synchronizer\Tests\Helpers\FakeSyncConfig;
use Grixu\Synchronizer\Tests\Helpers\SyncTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;

class ChunkLoadAndParseJobTest extends SyncTestCase
{
    use DatabaseMigrations;

    protected SyncConfig $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = FakeSyncConfig::makeChunkLoadAndParse();
    }

    /** @test */
    public function it_constructs()
    {
        $obj = new ChunkLoadAndParseJob($this->config);

        $this->assertEquals(ChunkLoadAndParseJob::class, $obj::class);
    }

    /** @test */
    public function it_run_in_queue()
    {
        Queue::fake();

        dispatch(new ChunkLoadAndParseJob($this->config));

        Queue::assertPushed(ChunkLoadAndParseJob::class, 1);
    }

    /** @test */
    public function it_run_in_batch()
    {
        Queue::fake();
        $bus = Bus::fake();

        $job = new ChunkLoadAndParseJob($this->config);
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
        $obj = new ChunkLoadAndParseJob($this->config);
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

        $job = new ChunkLoadAndParseJob($this->config);
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
        $job = new ChunkLoadAndParseJob($this->config);
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
