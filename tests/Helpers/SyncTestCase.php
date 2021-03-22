<?php

namespace Grixu\Synchronizer\Tests\Helpers;

use Grixu\Synchronizer\SynchronizerServiceProvider;

class SyncTestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            SynchronizerServiceProvider::class,
            \Spatie\LaravelRay\RayServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('socius-models.checksum_field', 'checksum');
        $app['config']->set('database.connections.xl', [
            'driver' => 'sqlite',
            'url' => null,
            'database' => __DIR__.'/../../database/test.sqlite',
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        try {
            $this->artisan('queue:batches-table')->run();
        } catch (\Exception) {}

        $this->artisan('migrate')->run();
    }

    protected function timestampConfig($app)
    {
        $app['config']->set('synchronizer.timestamps', ['updatedAt']);
    }
}
