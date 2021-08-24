<?php

namespace Grixu\Synchronizer\Engine\Transformer;

use Grixu\Synchronizer\Engine\Contracts\Map;
use Grixu\Synchronizer\Engine\Contracts\Transformer as TransformerInterface;

class Transformer implements TransformerInterface
{
    public function __construct(
        protected Map $map,
    ) {}

    public function sync(
        array $data,
        array $additional = []
    ): array {
        $synced = [];

        foreach ($this->map->get() as $inputField => $outputField) {
            if (isset($data[$inputField])) {
                $synced[$outputField] = $data[$inputField];
            } else {
                $synced[$outputField] = null;
            }
        }
        return array_merge($synced, $additional);
    }

    public function getMap(): Map
    {
        return $this->map;
    }
}
