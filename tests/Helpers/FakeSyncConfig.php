<?php

namespace Grixu\Synchronizer\Tests\Helpers;

use Grixu\SociusModels\Customer\Models\Customer;
use Grixu\Synchronizer\Config\SyncConfig;
use JetBrains\PhpStorm\Pure;

class FakeSyncConfig
{
    #[Pure]
    public static function make(): SyncConfig
    {
        return new SyncConfig(
            FakeLoader::class,
            FakeParser::class,
            Customer::class,
            'xlId'
        );
    }

    #[Pure]
    public static function makeArray(): array
    {
        return [
            FakeLoader::class,
            FakeParser::class,
            Customer::class,
            'xlId'
        ];
    }
}
