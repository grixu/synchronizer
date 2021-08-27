<?php

namespace Grixu\Synchronizer\Engine\Map;

class ExcludedField
{
    public function __construct(private string $field, private bool $fillable = true)
    {
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function isFillable(): bool
    {
        return $this->fillable;
    }
}
