<?php

namespace Grixu\Synchronizer\Tests\Helpers;

use Grixu\Synchronizer\SynchronizerServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [SynchronizerServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('socius-models.md5_local_model_field', 'checksum');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }

    protected function timestampConfig($app)
    {
        $app['config']->set('synchronizer.timestamps', ['updatedAt']);
    }
}
