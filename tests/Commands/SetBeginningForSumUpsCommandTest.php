<?php

namespace Grixu\Synchronizer\Tests\Commands;

use Grixu\Synchronizer\Tests\Helpers\BaseTestCase;
use Illuminate\Support\Facades\Cache;

/**
 * Class SetBeginningForSumUpsCommandTest
 * @package Grixu\Synchronizer\Tests\Commands
 */
class SetBeginningForSumUpsCommandTest extends BaseTestCase
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
        $this->artisan('synchronizer:set')
            ->expectsOutput('Done!')
            ->assertExitCode(0);
    }

    /** @test */
    public function cache()
    {
        $cacheDriver = app('cache')->driver();
        Cache::shouldReceive('driver')->andReturn($cacheDriver);

        Cache::shouldReceive('put')
            ->once();

        $this->artisan('synchronizer:set')
            ->expectsOutput('Done!')
            ->assertExitCode(0);
    }
}
