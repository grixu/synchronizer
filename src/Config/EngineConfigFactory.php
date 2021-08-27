<?php

namespace Grixu\Synchronizer\Config;

use Grixu\Synchronizer\Config\Contracts\EngineConfigInterface;

class EngineConfigFactory
{
    public static function make(
        string $model,
        string $key,
        array $fields = [],
        int $mode = EngineConfig::EXCLUDED,
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
            fields: $fields,
            mode: $mode,
            checksumField: $checksumField,
            timestamps: $timestamps,
            ids: $ids,
        );
    }
}
