<?php

namespace Grixu\Synchronizer\Tests\Process\Actions;

use Grixu\Synchronizer\Process\Actions\StartSyncAction;
use Grixu\Synchronizer\Process\Events\CollectionSynchronizedEvent;
use Grixu\Synchronizer\Tests\Helpers\FakeEngineConfig;
use Grixu\Synchronizer\Tests\Helpers\FakeProcessConfig;
use Grixu\Synchronizer\Tests\Helpers\SyncTestCase;
use Grixu\Synchronizer\Tests\Helpers\TestErrorHandler;
use Grixu\Synchronizer\Tests\Helpers\TestSyncHandler;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

class StartSyncActionTest extends SyncTestCase
{
    /** @test */
    public function full_sync()
    {
        $config = [
            [
                'process' => FakeProcessConfig::make(),
                'engine' => FakeEngineConfig::make(),
            ],
        ];
        $obj = new StartSyncAction($config);

        $this->runBatchAndCheckIt($obj, $config);
    }

    protected function runBatchAndCheckIt(StartSyncAction $obj, array $configs): \Illuminate\Bus\Batch
    {
        Queue::fake();
        $bus = Bus::fake();

        $batch = $obj->dispatch();

        $bus->assertBatched(
            function ($batch) use ($configs) {
                return $batch->jobs->count() == count($configs);
            }
        );

        return $batch;
    }

    /** @test */
    public function it_using_error_handling_from_config()
    {
        Http::fake();

        $config = [
            'process' => FakeProcessConfig::make(error: TestErrorHandler::class),
            'engine' => FakeEngineConfig::make(),
        ];
        $obj = new StartSyncAction($config);

        $this->runBatchAndCheckIt($obj, $config);

        Http::assertSent(function (Request $request) {
            return $request->url() == 'http://testable.dev';
        });
    }

    /** @test */
    public function it_could_use_array_of_sync_configs_too()
    {
        $config = [
            [
                'process' => FakeProcessConfig::makeArray(),
                'engine' => FakeEngineConfig::makeArray(),
            ],
        ];
        $obj = new StartSyncAction($config);

        $this->runBatchAndCheckIt($obj, $config);
    }

    /** @test */
    public function it_send_event_when_batch_finished()
    {
        Event::fake();

        $config = [
            [
                'process' => FakeProcessConfig::make(),
                'engine' => FakeEngineConfig::make(),
            ],
        ];
        $obj = new StartSyncAction($config);

        $batch = $this->runBatchAndCheckIt($obj, $config);

        $this->assertTrue($batch->finished());
        Event::assertDispatched(CollectionSynchronizedEvent::class, 1);
    }

    /** @test */
    public function it_runs_exception_handler_for_each_config()
    {
        Http::fake();

        $config = [
            'process' => FakeProcessConfig::make(sync: TestSyncHandler::class),
            'engine' => FakeEngineConfig::make(),
        ];
        $obj = new StartSyncAction($config);

        $this->runBatchAndCheckIt($obj, $config);

        Http::assertSent(function (Request $request) {
            return $request->url() == 'http://testable.dev';
        });
    }
}
