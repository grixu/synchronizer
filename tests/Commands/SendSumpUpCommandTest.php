<?php

namespace Grixu\Synchronizer\Tests\Commands;

use Grixu\Synchronizer\SynchronizerLogger;
use Grixu\Synchronizer\Tests\Helpers\BaseTestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Class SendSumpUpCommandTest
 * @package Grixu\Synchronizer\Tests
 */
class SendSumpUpCommandTest extends BaseTestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('logging.slack.url', 'k');
        $app['config']->set('synchronizer.send_slack_sum_up', true);
    }

    /** @test */
    public function walkthrough()
    {
        $this->artisan('synchronizer:send')
            ->expectsOutput('Done!')
            ->assertExitCode(0);
    }

    /** @test */
    public function cache()
    {
        $cacheDriver = app('cache')->driver();
        Cache::shouldReceive('driver')->andReturn($cacheDriver);

        Cache::shouldReceive('get')
            ->once()
            ->with('synchronizer-update', null)
            ->andReturn(now()->subDay());

        Cache::shouldReceive('put')
            ->once();

        $this->artisan('synchronizer:send')
            ->expectsOutput('Done!')
            ->assertExitCode(0);
    }

    /** @test */
    public function slack()
    {
        $testObj = new SynchronizerLogger('Product', 1);
        $testObj->addChanges('name', 'name', 'lol', 'LOL');
        $testObj->save();

        $this->assertDatabaseCount('synchronizer_logs', 1);

        Log::shouldReceive('channel')->once()->andReturnSelf();
        Log::shouldReceive('notice')->once();

        $this->artisan('synchronizer:send')
            ->expectsOutput('Done!')
            ->assertExitCode(0);
    }
}
