<?php

namespace Grixu\Synchronizer\Config\Contracts;

interface EngineConfigInterface
{
    public function getModel(): string;
    public function getKey(): string;
    public function getIds(): array;
    public function getTimestamps(): array;
    public function getTimestampsAsSnake(): array;
    public function getChecksumField(): string | null;
    public function getChecksumFieldAsSnake(): string | null;
    public function getExcluded(): array;
    public function getFillable(): array;
    public function getOnly(): array;
    public function isOnlyMode(): bool;
}
