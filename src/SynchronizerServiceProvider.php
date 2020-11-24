<?php

namespace Grixu\Synchronizer;

use Grixu\Synchronizer\Console\AddExcludedFieldCommand;
use Grixu\Synchronizer\Console\ListExcludedFieldsCommand;
use Grixu\Synchronizer\Console\SendSumUpCommand;
use Grixu\Synchronizer\Console\SetBeginningForSumUpsCommand;
use Illuminate\Support\ServiceProvider;

/**
 * Class SynchronizerServiceProvider
 * @package Grixu\Synchronizer
 */
class SynchronizerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes(
                [
                    __DIR__ . '/../config/config.php' => config_path('synchronizer.php'),
                ],
                'config'
            );

            // Registering package commands.
            $this->commands(
                [
                    AddExcludedFieldCommand::class,
                    ListExcludedFieldsCommand::class,
                    SendSumUpCommand::class,
                    SetBeginningForSumUpsCommand::class
                ]
            );
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'synchronizer');

        // Register the main class to use with the facade
        $this->app->singleton(
            'synchronizer',
            function () {
                return new SynchronizerFactory();
            }
        );
    }
}
