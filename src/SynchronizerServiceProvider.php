<?php

namespace Grixu\Synchronizer;

use Grixu\Synchronizer\Engine\Config\EngineConfig;
use Grixu\Synchronizer\Engine\Contracts\EngineConfigInterface;
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
