<?php

namespace Grixu\Synchronizer\Engine\Map;

use Grixu\Synchronizer\Engine\Contracts\Map as MapInterface;
use JetBrains\PhpStorm\Pure;

class NullMap implements MapInterface
{
    public function add(string $field, ?string $modelField = null): void
    {
        return;
    }

    public function get(): array
    {
        return [];
    }

    public function getWithoutTimestamps(): array
    {
        return [];
    }

    public function getModelFieldsArray(): array
    {
        return [];
    }

    public function getUpdatableOnNullFields(): array
    {
        return [];
    }

    #[Pure]
    public static function make(): MapInterface
    {
        return new static();
    }
}
