<?php

namespace Grixu\Synchronizer\Engine\Transformer;

use Grixu\Synchronizer\Engine\Contracts\Map;
use Grixu\Synchronizer\Engine\Contracts\Transformer as TransformerInterface;
use Grixu\Synchronizer\Engine\Map\NullMap;

class NullTransformer implements TransformerInterface
{
    public function sync(array $data, array $additional = []): array
    {
        return $data;
    }

    public function getMap(): Map
    {
        return NullMap::make();
    }

    public static function make(): static
    {
        return new static();
    }
}
