<?php

namespace Grixu\Synchronizer;

use Grixu\Synchronizer\Models\ExcludedField;

class MapEntry
{
    protected string $dtoField;
    protected string $modelField;
    protected bool $updateOnNull = false;
    protected bool $isExcluded = false;

    public function __construct(string $dtoField, string $modelField, ?ExcludedField $excludedField = null)
    {
        $this->dtoField = $dtoField;
        $this->modelField = $modelField;

        if (!empty($excludedField)) {
            $this->isExcluded = true;

            if ($excludedField->update_empty == true) {
                $this->updateOnNull = true;
            }
        }
    }

    public function isSyncable($value = null): bool
    {
        return !$this->isExcluded || ($this->updateOnNull && empty($value));
    }

    public function isTimestamp(): bool
    {
        return in_array($this->modelField, config('synchronizer.sync.timestamps'));
    }

    public function getDtoField(): string
    {
        return $this->dtoField;
    }

    public function getModelField(): string
    {
        return $this->modelField;
    }
}
