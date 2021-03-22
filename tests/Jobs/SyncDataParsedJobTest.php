<?php

namespace Grixu\Synchronizer\Tests\Jobs;

use Grixu\SociusModels\Customer\Models\Customer;
use Grixu\Synchronizer\Config\SyncConfig;
use Grixu\Synchronizer\Jobs\SyncDataParsedJob;
use Grixu\Synchronizer\Tests\Helpers\FakeLoader;
use Grixu\Synchronizer\Tests\Helpers\FakeParser;
use Grixu\Synchronizer\Tests\Helpers\FakeSyncConfig;
use Grixu\Synchronizer\Tests\Helpers\SyncTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

class SyncDataParsedJobTest extends SyncTestCase
{
    use DatabaseMigrations;

    protected SyncConfig $config;
    protected Collection $dtoCollection;

    protected function setUp(): void
    {
        parent::setUp();

        require_once __DIR__.'/../../vendor/grixu/socius-models/migrations/customer/2020_09_30_102037_create_customers_table.php';
        (new \CreateCustomersTable())->up();

        $this->config = FakeSyncConfig::make();

        $loader = new FakeLoader();
        $dataCollection = $loader->get()->first();

        $parser = new FakeParser();
        $this->dtoCollection = $dataCollection->map(fn($item) => $parser->parse($item));
    }

    /** @test */
    public function it_constructs()
    {
        $obj = new SyncDataParsedJob($this->dtoCollection, $this->config);

        $this->assertEquals(SyncDataParsedJob::class, $obj::class);
    }

    /** @test */
    public function it_run_in_queue()
    {
        Queue::fake();

        dispatch(new SyncDataParsedJob($this->dtoCollection, $this->config));

        Queue::assertPushed(SyncDataParsedJob::class, 1);
    }

    /** @test */
    public function it_run_in_batch()
    {
        Queue::fake();
        $bus = Bus::fake();

        $job = new SyncDataParsedJob($this->dtoCollection, $this->config);
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
    public function it_syncs_data()
    {
        $obj = new SyncDataParsedJob($this->dtoCollection, $this->config);

        $this->assertDatabaseCount('customers', 0);

        $obj->handle();

        $this->assertTrue(Customer::count() > 0);
    }

    /** @test */
    public function it_skips_damaged_data()
    {
        $workingDto = new \stdClass();
        $workingDto->name = 'Customer';
        $workingDto->xlId = 1;
        $workingDto->updatedAt = now();

        $brokenDto = new \stdClass();
        $brokenDto->name = 'Some other language';

        $this->dtoCollection = collect(
            [
                $workingDto,
                $brokenDto
            ]
        );
        $this->assertDatabaseCount('customers', 0);

        $obj = new SyncDataParsedJob($this->dtoCollection, $this->config);
        $obj->handle();

        $this->assertTrue(Customer::count() == 0);
    }

    /** @test */
    public function it_fails_on_type_error()
    {
        Http::fake();

        $this->config->setErrorHandler(function ($e) {
            Http::get('http://testable.dev');
        });

        $brokenDto = new \stdClass();
        $brokenDto->name = null;
        $brokenDto->xlId = 1;
        $brokenDto->updatedAt = now();

        $this->dtoCollection->push($brokenDto);

        ray($this->dtoCollection);

        $obj = new SyncDataParsedJob($this->dtoCollection, $this->config);

        $this->assertDatabaseCount('customers', 0);

        $obj->handle();

        $this->assertTrue(Customer::count() > 0);
        Http::assertSent(function (Request $request) {
            return $request->url() == 'http://testable.dev';
        });
    }
}