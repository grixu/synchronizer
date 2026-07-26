<?php

namespace Grixu\Synchronizer\Engine\Map;

use Spatie\LaravelData\Data;

class MapFactory
{
    public static function makeFromDto(Data $dto): Map
    {
        return new Map(array_keys($dto->toArray()));
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
