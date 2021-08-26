<?php

namespace Grixu\Synchronizer\Tests\Helpers;

use Grixu\SociusModels\Customer\Models\Customer;
use Grixu\Synchronizer\Config\Contracts\EngineConfigInterface;
use Grixu\Synchronizer\Config\EngineConfigFactory;

class FakeEngineConfig
{
    public static function make(
        string $model = Customer::class,
        string $key = 'xlId',
        array $timestamps = [],
        string|bool|null $checksumField = null,
        array $excludedFields = [],
        array $ids = [],
    ): EngineConfigInterface {
        return EngineConfigFactory::make(
            model: $model,
            key: $key,
            excludedFields: $excludedFields,
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
            config('synchronizer.checksum.field'),
            config('synchronizer.checksum.timestamps'),
        ];
    }
}
