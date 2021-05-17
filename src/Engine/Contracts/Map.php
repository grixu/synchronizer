<?php

namespace Grixu\Synchronizer\Engine\Contracts;

interface Map
{
    public function add(string $field, string|null $modelField = null): void;
    public function get(): array;
    public function getWithoutTimestamps(): array;
    public function getModelFieldsArray(): array;
    public function getUpdatableOnNullFields(): array;
}
