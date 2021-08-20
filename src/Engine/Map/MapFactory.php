<?php

namespace Grixu\Synchronizer\Engine\Map;

use Spatie\DataTransferObject\DataTransferObject;

class MapFactory
{
    public static function makeFromDto(DataTransferObject $dataTransferObject, string $model, string|null $checksumField = null): Map
    {
        return new Map(array_keys($dataTransferObject->toArray()), $model, $checksumField);
    }

    public static function makeFromArray(array $dataArray, string $model, string|null $checksumField = null): Map
    {
        return new Map(array_keys($dataArray), $model, $checksumField);
    }

    public static function make(array $fields, string $model, string|null $checksumField = null): Map
    {
        return new Map($fields, $model, $checksumField);
    }
}
