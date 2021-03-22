<?php

namespace Grixu\Synchronizer\Tests\Actions;

use Grixu\Synchronizer\Actions\StartSyncAction;
use Grixu\Synchronizer\Tests\Helpers\FakeLoader;
use Grixu\Synchronizer\Tests\Helpers\FakeParser;
use Grixu\Synchronizer\Tests\Helpers\FakeSyncConfig;
use Grixu\Synchronizer\Tests\Helpers\SyncTestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\Request;
use Illuminate\Queue\SerializableClosure;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

class StartSyncActionTest extends SyncTestCase
{
    protected StartSyncAction $obj;

    protected function setUp(): void
    {
        parent::setUp();
        $this->obj = new StartSyncAction();
    }

    /** @test */
    public function full_sync()
    {
        $this->runBatchAndCheckIt(
            [
                'customer' => FakeSyncConfig::makeArray(),
                'another_but_same' => FakeSyncConfig::makeArray()
            ]
        );
    }

    protected function runBatchAndCheckIt(array $config)
    {
        Queue::fake();
        $bus = Bus::fake();

        $this->obj->execute($config);

        $bus->assertBatched(
            function ($batch) use ($config) {
                return $batch->jobs->count() == count($config);
            }
        );
    }

    /** @test */
    public function one_module()
    {
        $this->runBatchAndCheckIt([FakeSyncConfig::makeArray()]);
    }

    /** @test */
    public function it_using_error_handling_from_config()
    {
        Http::fake();

        $config = [
            'customer' => [
                FakeLoader::class,
                FakeParser::class,
                Model::class,
                'xlId',
                null,
                null,
                new SerializableClosure(function() {
                    Http::get('http://testable.dev');
                })
            ]
        ];

        $this->obj->execute($config, 'sync');

        Http::assertSent(function (Request $request) {
            return $request->url() == 'http://testable.dev';
        });
    }
}
