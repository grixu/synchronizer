<?php

namespace Grixu\Synchronizer;

use Grixu\Synchronizer\Config\Contracts\EngineConfigInterface;
use Grixu\Synchronizer\Config\EngineConfig;
use Grixu\Synchronizer\Console\AddExcludedFieldCommand;
use Grixu\Synchronizer\Console\ListExcludedFieldsCommand;
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

        $this->app->bind(EngineConfigInterface::class, function () {
            return EngineConfig::getInstance();
        });

        $this->app->register(EventServiceProvider::class);
    }
}
