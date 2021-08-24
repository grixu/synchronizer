<?php

namespace Grixu\Synchronizer\Tests\Helpers;

use Grixu\SociusModels\Customer\Models\Customer;
use Grixu\Synchronizer\Config\SyncConfig;

class FakeSyncConfig
{
    public static function make(string $checksum = 'checksum', array $timestamps = []): SyncConfig
    {
        return new SyncConfig(
            FakeLoader::class,
            FakeParser::class,
            Customer::class,
            'xlId',
            config('synchronizer.jobs.default'),
            $checksum,
            $timestamps
        );
    }

    public static function makeLoadAllAndParse(): SyncConfig
    {
        return new SyncConfig(
            FakeLoader::class,
            FakeParser::class,
            Customer::class,
            'xlId',
            config('synchronizer.jobs.load-all-and-parse'),
            config('synchronizer.checksum.field')
        );
    }

    public static function makeChunkLoadAndParse(): SyncConfig
    {
        return new SyncConfig(
            FakeLoader::class,
            FakeParser::class,
            Customer::class,
            'xlId',
            config('synchronizer.jobs.chunk-load-and-parse'),
            config('synchronizer.checksum.field')
        );
    }

    public static function makeArray(): array
    {
        return [
            FakeLoader::class,
            FakeParser::class,
            Customer::class,
            'xlId',
            config('synchronizer.jobs.default'),
            config('synchronizer.checksum.field'),
        ];
    }

    public static function makeWithCustomModel(string $model, string $checksum = 'checksum', array $timestamps = []): SyncConfig
    {
        return new SyncConfig(
            FakeLoader::class,
            FakeParser::class,
            $model,
            'xlId',
            config('synchronizer.jobs.default'),
            $checksum,
            $timestamps
        );
    }
}
