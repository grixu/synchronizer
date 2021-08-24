<?php

namespace Grixu\Synchronizer\Engine\Map;

use Grixu\Synchronizer\Config\Contracts\SyncConfig;
use Spatie\DataTransferObject\DataTransferObject;

class MapFactory
{
    public static function makeFromDto(DataTransferObject $dataTransferObject): Map
    {
        return new Map(array_keys($dataTransferObject->toArray()));
    }

    public static function makeFromArray(array $dataArray): Map
    {
        return new Map(array_keys($dataArray));
    }

    public static function make(array $fields): Map
    {
        return new Map($fields);
    }
}
