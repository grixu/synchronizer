<?php

namespace Grixu\Synchronizer\Tests\Helpers;

use Grixu\Synchronizer\Config\Contracts\ProcessConfigInterface;
use Grixu\Synchronizer\Engine\Contracts\EngineConfigInterface;
use Grixu\Synchronizer\SynchronizerServiceProvider;

class SyncTestCase extends \Orchestra\Testbench\TestCase
{
    protected ProcessConfigInterface $processConfig;
    protected EngineConfigInterface $engineConfig;

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

        require_once __DIR__ . '/../../vendor/grixu/socius-models/migrations/create_customers_table.stub';
        try {
            (new \CreateCustomersTable())->up();
        } catch (\Throwable) {
        }

        try {
            $this->artisan('queue:batches-table')->run();
        } catch (\Exception) {
        }

        $this->artisan('migrate')->run();

        $this->processConfig = FakeProcessConfig::make();
        $this->engineConfig = FakeEngineConfig::make();
    }

    protected function timestampConfig($app)
    {
        $app['config']->set('synchronizer.sync.timestamps', ['updatedAt']);
    }
}
