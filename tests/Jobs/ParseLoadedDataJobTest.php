<?php

namespace Grixu\Synchronizer\Tests\Jobs;

use Grixu\Synchronizer\Config\SyncConfig;
use Grixu\Synchronizer\Jobs\ParseLoadedDataJob;
use Grixu\Synchronizer\Tests\Helpers\FakeLoader;
use Grixu\Synchronizer\Tests\Helpers\FakeCancelJob;
use Grixu\Synchronizer\Tests\Helpers\FakeSyncConfig;
use Grixu\Synchronizer\Tests\Helpers\SyncTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;

class ParseLoadedDataJobTest extends SyncTestCase
{
    use DatabaseMigrations;

    protected SyncConfig $config;
    protected Collection $dataCollection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = FakeSyncConfig::make();

        $loader = new FakeLoader();
        $this->dataCollection = $loader->get()->first();
    }

    /** @test */
    public function it_constructs()
    {
        $obj = new ParseLoadedDataJob($this->dataCollection, $this->config);

        $this->assertEquals(ParseLoadedDataJob::class, $obj::class);
    }

    /** @test */
    public function it_run_in_queue()
    {
        Queue::fake();

        dispatch(new ParseLoadedDataJob($this->dataCollection, $this->config));

        Queue::assertPushed(ParseLoadedDataJob::class, 1);
    }

    /** @test */
    public function it_run_in_batch()
    {
        Queue::fake();
        $bus = Bus::fake();

        $job = new ParseLoadedDataJob($this->dataCollection, $this->config);
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
        ray()->disable();

        $obj = new ParseLoadedDataJob($this->dataCollection, $this->config);
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
    public function it_reacts_to_cancellation()
    {
        $job = new ParseLoadedDataJob($this->dataCollection, $this->config);
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
