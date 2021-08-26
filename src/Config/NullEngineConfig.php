<?php

namespace Grixu\Synchronizer\Config;

use Grixu\Synchronizer\Config\Contracts\EngineConfig;

class NullEngineConfig implements EngineConfig
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

    public function getExcludedFields(): array
    {
        return [];
    }
}
