<?php

namespace Grixu\Synchronizer;

use JetBrains\PhpStorm\Pure;

class ModelSynchronizer
{
    public function __construct(
        protected Map $map,
        protected string|null $checksumField = null
    ) {
    }

    #[Pure]
    public function sync(array $data): array
    {
        $synced = [];

        foreach ($this->map->get() as $inputField => $outputField) {
            $synced[$outputField] = $data[$inputField];
        }

        if ($this->checksumField) $synced[$this->checksumField] = $data[$this->checksumField];

        return $synced;
    }
}
