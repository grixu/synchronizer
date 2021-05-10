<?php

namespace Grixu\Synchronizer;

use Grixu\Synchronizer\Config\SyncConfigFactory;
use Grixu\Synchronizer\Console\AddExcludedFieldCommand;
use Grixu\Synchronizer\Console\ListExcludedFieldsCommand;
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

        Checksum::setChecksumField(config('synchronizer.checksum.field'));
        Map::setTimestamps(config('synchronizer.sync.timestamps'));

        $this->app->singleton(SyncConfigFactory::class, function () {
            return new SyncConfigFactory();
        });
    }
}
