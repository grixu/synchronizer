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
