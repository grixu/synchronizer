<?php

namespace Grixu\Synchronizer\Engine\Map;

use Spatie\DataTransferObject\DataTransferObject;

class MapFactory
{
    public static function makeFromDto(DataTransferObject $dataTransferObject, string $model): Map
    {
        return new Map(array_keys($dataTransferObject->toArray()), $model);
    }

    public static function makeFromArray(array $dataArray, string $model): Map
    {
        return new Map(array_keys($dataArray), $model);
    }

    public static function make(array $fields, string $model): Map
    {
        return new Map($fields, $model);
    }
}
