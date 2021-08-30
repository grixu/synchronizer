<?php

namespace Grixu\Synchronizer\Engine\Config;

use Grixu\Synchronizer\Engine\Contracts\EngineConfigInterface;

class NullEngineConfig implements EngineConfigInterface
{
    public function getModel(): string
    {
        return '';
    }

    public function getKey(): string
    {
        return '';
    }

    public function getIds(): array
    {
        return [];
    }

    public function getTimestamps(): array
    {
        return [];
    }

    public function getChecksumField(): string|null
    {
        return null;
    }

    public function getExcluded(): array
    {
        return [];
    }

    public function getFillable(): array
    {
        return [];
    }

    public function getOnly(): array
    {
        return [];
    }

    public function isOnlyMode(): bool
    {
        return false;
    }
}
