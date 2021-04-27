<?php

namespace Grixu\Synchronizer\Tests\Helpers;

use Grixu\Synchronizer\SynchronizerServiceProvider;
use Spatie\LaravelRay\RayServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            SynchronizerServiceProvider::class,
            RayServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('socius-models.checksum_field', 'checksum');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }

    protected function timestampConfig($app)
    {
        $app['config']->set('synchronizer.sync.timestamps', ['updated_at']);
    }
}
