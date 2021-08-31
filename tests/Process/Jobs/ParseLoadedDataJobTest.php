<?php

namespace Grixu\Synchronizer\Tests\Process\Jobs;

use Grixu\Synchronizer\Process\Jobs\ParseLoadedDataJob;
use Grixu\Synchronizer\Tests\Helpers\FakeCancelJob;
use Grixu\Synchronizer\Tests\Helpers\FakeLoader;
use Grixu\Synchronizer\Tests\Helpers\SyncTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;

class ParseLoadedDataJobTest extends SyncTestCase
{
    use DatabaseMigrations;

    protected Collection $dataCollection;

    /** @test */
    public function it_constructs()
    {
        $obj = new ParseLoadedDataJob($this->processConfig, $this->engineConfig, $this->dataCollection);

        $this->assertEquals(ParseLoadedDataJob::class, $obj::class);
    }

    /** @test */
    public function it_run_in_queue()
    {
        Queue::fake();

        dispatch(new ParseLoadedDataJob($this->processConfig, $this->engineConfig, $this->dataCollection));

        Queue::assertPushed(ParseLoadedDataJob::class, 1);
    }

    /** @test */
    public function it_run_in_batch()
    {
        Queue::fake();
        $bus = Bus::fake();

        $job = new ParseLoadedDataJob($this->processConfig, $this->engineConfig, $this->dataCollection);
        $batch = $bus->batch(
            [
                $job,
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
        $obj = new ParseLoadedDataJob($this->processConfig, $this->engineConfig, $this->dataCollection);
        $batch = Bus::batch(
            [
                $obj,
            ]
        )
            ->allowFailures()
            ->dispatch();

        $this->assertGreaterThan(1, $batch->totalJobs);
    }

    /** @test */
    public function it_reacts_to_cancellation()
    {
        $job = new ParseLoadedDataJob($this->processConfig, $this->engineConfig, $this->dataCollection);
        $batch = Bus::batch(
            [
                (new FakeCancelJob()),
                $job->delay(1000),
            ]
        );

        $finishedBatch = $batch->dispatch();

        $this->assertTrue($finishedBatch->cancelled());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $loader = new FakeLoader();
        $this->dataCollection = $loader->get()->first();
        $this->processConfig->setCurrentJob(1);
    }
}
