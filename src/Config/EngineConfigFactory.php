<?php

namespace Grixu\Synchronizer\Config;

class EngineConfigFactory
{
    public static function make(
        string $model,
        string $key,
        array $excludedFields = [],
        string|bool|null $checksumField = null,
        array|bool $timestamps = [],
        array $ids = [],
    ): EngineConfig {
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
            excludedFields: $excludedFields,
            checksumField: $checksumField,
            timestamps: $timestamps,
            ids: $ids,
        );
    }
}
