<?php

namespace Grixu\Synchronizer\Tests\Helpers;

use Grixu\SociusModels\Customer\Models\Customer;
use Grixu\Synchronizer\Config\SyncConfig;

class FakeSyncConfig
{
    public static function make(): SyncConfig
    {
        return new SyncConfig(
            FakeLoader::class,
            FakeParser::class,
            Customer::class,
            'xlId',
            config('synchronizer.jobs.default')

        );
    }

    public static function makeLoadAllAndParse(): SyncConfig
    {
        return new SyncConfig(
            FakeLoader::class,
            FakeParser::class,
            Customer::class,
            'xlId',
            config('synchronizer.jobs.load-all-and-parse')

        );
    }

    public static function makeChunkLoadAndParse(): SyncConfig
    {
        return new SyncConfig(
            FakeLoader::class,
            FakeParser::class,
            Customer::class,
            'xlId',
            config('synchronizer.jobs.chunk-load-and-parse')

        );
    }

    public static function makeArray(): array
    {
        return [
            FakeLoader::class,
            FakeParser::class,
            Customer::class,
            'xlId',
            config('synchronizer.jobs.default')
        ];
    }
}
