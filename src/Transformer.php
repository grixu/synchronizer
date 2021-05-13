<?php

namespace Grixu\Synchronizer;

use JetBrains\PhpStorm\Pure;

class Transformer
{
    protected string|null $checksumField;

    public function __construct(
        protected Map $map,
    ) {
        $this->checksumField = Checksum::$checksumField;
    }

    #[Pure]
    public function sync(
        array $data,
        array $additional = []
    ): array {
        $synced = [];

        foreach ($this->map->get() as $inputField => $outputField) {
            if (isset($data[$inputField])) {
                $synced[$outputField] = $data[$inputField];
            }
        }

        return array_merge($synced, $additional);
    }

    public function getMap(): Map
    {
        return $this->map;
    }
}
