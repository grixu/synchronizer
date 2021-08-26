<?php

namespace Grixu\Synchronizer\Config\Contracts;

interface EngineConfigInterface
{
    public function getModel(): string;
    public function getKey(): string;
    public function getIds(): array;
    public function getTimestamps(): array;
    public function getChecksumField(): string | null;
    public function getExcludedFields(): array;
}
