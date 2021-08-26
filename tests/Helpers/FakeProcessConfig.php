<?php

namespace Grixu\Synchronizer\Tests\Helpers;

use Grixu\Synchronizer\Config\ProcessConfig;
use Grixu\Synchronizer\Process\Handlers\DefaultErrorHandler;
use Grixu\Synchronizer\Process\Handlers\DefaultSyncHandler;

class FakeProcessConfig
{
    public static function make(string $jobs = 'default', string|null $sync = null, string|null $error = null): ProcessConfig
    {
        if (empty($sync)) {
            $sync = DefaultSyncHandler::class;
        }

        if (empty($error)) {
            $error = DefaultErrorHandler::class;
        }

        return ProcessConfig::make(
            loaderClass: FakeLoader::class,
            parserClass: FakeParser::class,
            jobsConfig: $jobs,
            syncHandler: $sync,
            errorHandler: $error
        );
    }

    public static function makeArray(): array
    {
        return [
            FakeLoader::class,
            FakeParser::class,
            config('synchronizer.jobs.default'),
            config('synchronizer.handlers.sync'),
            config('synchronizer.handlers.error'),
        ];
    }
}
