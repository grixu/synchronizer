<?php

namespace Grixu\Synchronizer;

use Grixu\Synchronizer\Config\Contracts\SyncConfig as SyncConfigInterface;
use Grixu\Synchronizer\Config\NullSyncConfig;
use Grixu\Synchronizer\Config\SyncConfig;
use Grixu\Synchronizer\Config\SyncConfigFactory;
use Grixu\Synchronizer\Console\AddExcludedFieldCommand;
use Grixu\Synchronizer\Console\ListExcludedFieldsCommand;
use Grixu\Synchronizer\Engine\Map\Map;
use Grixu\Synchronizer\Providers\EventServiceProvider;
use Illuminate\Support\ServiceProvider;

class SynchronizerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes(
                [
                    __DIR__ . '/../config/config.php' => config_path('synchronizer.php'),
                ],
                'config'
            );

            $this->commands(
                [
                    AddExcludedFieldCommand::class,
                    ListExcludedFieldsCommand::class,
                ]
            );
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'synchronizer');

        $this->app->bind(SyncConfigInterface::class, function () {
            return SyncConfig::getInstance();
        });

        $this->app->register(EventServiceProvider::class);

        $this->app->singleton(SyncConfigFactory::class, function () {
            return new SyncConfigFactory();
        });
    }
}
