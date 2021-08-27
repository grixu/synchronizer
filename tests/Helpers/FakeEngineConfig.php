<?php

namespace Grixu\Synchronizer\Tests\Helpers;

use Grixu\SociusModels\Customer\Models\Customer;
use Grixu\Synchronizer\Config\Contracts\EngineConfigInterface;
use Grixu\Synchronizer\Config\EngineConfig;
use Grixu\Synchronizer\Config\EngineConfigFactory;

class FakeEngineConfig
{
    public static function make(
        string $model = Customer::class,
        string $key = 'xlId',
        array $timestamps = [],
        string|bool|null $checksumField = null,
        array $fields = [],
        int $mode = EngineConfig::EXCLUDED,
        array $ids = [],
    ): EngineConfigInterface {
        return EngineConfigFactory::make(
            model: $model,
            key: $key,
            fields: $fields,
            mode: $mode,
            checksumField: $checksumField,
            timestamps: $timestamps,
            ids: $ids,
        );
    }

    public static function makeArray(): array
    {
        return [
            Customer::class,
            'xlId',
            [],
            EngineConfig::EXCLUDED,
            config('synchronizer.checksum.field'),
            config('synchronizer.checksum.timestamps'),
        ];
    }
}
