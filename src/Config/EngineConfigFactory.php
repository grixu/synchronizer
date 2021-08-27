<?php

namespace Grixu\Synchronizer\Config;

use Grixu\Synchronizer\Config\Contracts\EngineConfigInterface;
use Illuminate\Support\Str;

class EngineConfigFactory
{
    public static function make(
        string $model,
        string $key,
        array $excludedFields = [],
        string|bool|null $checksumField = null,
        array|bool $timestamps = [],
        array $ids = [],
    ): EngineConfigInterface {
        if (config('synchronizer.checksum.control')) {
            if (empty($checksumField) && $checksumField !== false) {
                $checksumField = config('synchronizer.checksum.field');
            }

            if ($checksumField === false) {
                $checksumField = null;
            }

            if (empty($timestamps) && $timestamps !== false) {
                $timestamps = config('synchronizer.checksum.timestamps');
            }

            if ($timestamps === false) {
                $timestamps = [];
            }
        }

        return new EngineConfig(
            model: $model,
            key: $key,
            excludedFields: array_map(fn ($item) => Str::camel($item), $excludedFields),
            checksumField: Str::camel($checksumField),
            timestamps: array_map(fn ($item) => Str::camel($item), $timestamps),
            ids: $ids,
        );
    }
}
