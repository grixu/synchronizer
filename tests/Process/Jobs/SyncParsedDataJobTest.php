<?php

namespace Grixu\Synchronizer\Tests\Process\Jobs;

use Grixu\SociusModels\Customer\Models\Customer;
use Grixu\Synchronizer\Process\Jobs\SyncParsedDataJob;
use Grixu\Synchronizer\Tests\Helpers\FakeLoader;
use Grixu\Synchronizer\Tests\Helpers\FakeParser;
use Grixu\Synchronizer\Tests\Helpers\FakeCancelJob;
use Grixu\Synchronizer\Tests\Helpers\SyncTestCase;
use Grixu\Synchronizer\Tests\Helpers\TestErrorHandler;
use Grixu\Synchronizer\Tests\Helpers\TestSyncHandler;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use stdClass;

class SyncParsedDataJobTest extends SyncTestCase
{
    use DatabaseMigrations;

    protected array $data;

    protected function setUp(): void
    {
        parent::setUp();

        $loader = new FakeLoader();
        $dataCollection = $loader->get()->first();

        $parser = new FakeParser();
        $this->data = $parser->parse($dataCollection)->toArray();

        $this->processConfig->setCurrentJob(2);
    }

    /** @test */
    public function it_constructs()
    {
        $obj = new SyncParsedDataJob($this->processConfig, $this->engineConfig, $this->data);

        $this->assertEquals(SyncParsedDataJob::class, $obj::class);
    }

    /** @test */
    public function it_run_in_queue()
    {
        Queue::fake();

        dispatch(new SyncParsedDataJob($this->processConfig, $this->engineConfig, $this->data));

        Queue::assertPushed(SyncParsedDataJob::class, 1);
    }

    /** @test */
    public function it_run_in_batch()
    {
        Queue::fake();
        $bus = Bus::fake();

        $job = new SyncParsedDataJob($this->processConfig, $this->engineConfig, $this->data);
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
    public function it_syncs_data()
    {
        $obj = new SyncParsedDataJob($this->processConfig, $this->engineConfig, $this->data);

        $this->assertDatabaseCount('customers', 0);

        $obj->handle();

        $this->assertTrue(Customer::count() > 0);
    }

    /** @test */
    public function it_skips_damaged_data()
    {
        $workingDto = new stdClass();
        $workingDto->name = 'Customer';
        $workingDto->xlId = 1;
        $workingDto->updatedAt = now();

        $brokenDto = new stdClass();
        $brokenDto->name = 'Some other language';

        $this->data = collect(
            [
                $workingDto,
                $brokenDto,
            ]
        )->toArray();
        $this->assertDatabaseCount('customers', 0);

        $obj = new SyncParsedDataJob($this->processConfig, $this->engineConfig, $this->data);
        $obj->handle();

        $this->assertTrue(Customer::count() == 0);
    }

    /** @test */
    public function it_fails_on_type_error()
    {
        Http::fake();

        $this->processConfig->setErrorHandler(TestErrorHandler::class);

        $brokenDto = new stdClass();
        $brokenDto->name = null;
        $brokenDto->xlId = 1;
        $brokenDto->updatedAt = now();

        $this->data[] = $brokenDto;

        $obj = new SyncParsedDataJob($this->processConfig, $this->engineConfig, $this->data);

        $this->assertDatabaseCount('customers', 0);

        $obj->handle();

        $this->assertDatabaseCount('customers', 0);

        Http::assertSent(function (Request $request) {
            return $request->url() == 'http://testable.dev';
        });
    }

    /** @test */
    public function it_reacts_to_cancellation()
    {
        $job = new SyncParsedDataJob($this->processConfig, $this->engineConfig, $this->data);
        $batch = Bus::batch(
            [
                (new FakeCancelJob()),
                $job->delay(1000),
            ]
        );

        $finishedBatch = $batch->dispatch();

        $this->assertTrue($finishedBatch->cancelled());
    }

    /** @test */
    public function check_sync_closure_execution()
    {
        Http::fake();

        $this->processConfig->setSyncHandler(TestSyncHandler::class);

        $obj = new SyncParsedDataJob($this->processConfig, $this->engineConfig, $this->data);

        $obj->handle();

        Http::assertSent(function (Request $request) {
            return $request->url() == 'http://testable.dev';
        });
    }
}
