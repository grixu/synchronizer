<?php

namespace Grixu\Synchronizer;

use Illuminate\Support\Str;
use Spatie\DataTransferObject\DataTransferObject;

class MapFactory
{
    public static function makeFromDto(DataTransferObject $dataTransferObject, string $model): Map
    {
        $map = [];

        foreach ($dataTransferObject->except('relationships')->toArray() as $key => $value) {
            $map[$key] = Str::snake($key);
        }

        return new Map($map, $model);
    }

    public static function makeFromArray(array $dataArray, string $model): Map
    {
        $map = [];

        foreach ($dataArray as $key => $value) {
            $map[$key] = Str::snake($key);
        }

        return new Map($map, $model);
    }

    public static function make(array $map, string $model): Map
    {
        return new Map($map, $model);
    }
}
