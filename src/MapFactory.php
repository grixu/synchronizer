<?php

namespace Grixu\Synchronizer;

use Spatie\DataTransferObject\DataTransferObject;

class MapFactory
{
    public static function makeFromDto(DataTransferObject $dataTransferObject, string $model): Map
    {
        $map = [];

        foreach ($dataTransferObject->toArray() as $key => $value) {
            $map[$key] = $key;
        }

        return new Map($map, $model);
    }

    public static function makeFromArray(array $map, string $model): Map
    {
        return new Map($map, $model);
    }
}
