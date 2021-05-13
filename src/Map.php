<?php

namespace Grixu\Synchronizer;

use Grixu\Synchronizer\Models\ExcludedField;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Map
{
    protected array $map = [];
    protected array $mapWithoutTimestamps = [];
    protected array $updatableOnNull = [];
    protected static array $timestamps;

    public function __construct(array $fields, protected string $model)
    {
        $excludedFields = $this->getExcludedFields($model);

        if (!empty(Checksum::$checksumField)) {
            $fields[] = Checksum::$checksumField;
        }

        unset($fields['relations']);

        foreach ($fields as $field) {
            $modelField = Str::snake($field);

            $excludedField = $excludedFields->where('field', $modelField)->first();

            if ($excludedField) {
                if ($excludedField->update_empty) {
                    $this->updatableOnNull[] = $modelField;
                }

                continue;
            }

            $this->map[$field] = $modelField;

            if (!in_array($modelField, static::$timestamps)) {
                $this->mapWithoutTimestamps[$field] = $modelField;
            }
        }
    }

    protected function getExcludedFields(string $model): Collection
    {
        return ExcludedField::query()
            ->where('model', $model)
            ->get();
    }

    public function get(): array
    {
        return $this->map;
    }

    public function getWithoutTimestamps(): array
    {
        return $this->mapWithoutTimestamps;
    }

    public function getModelFieldsArray(): array
    {
        return array_values($this->map);
    }

    public function getUpdatableOnNullFields(): array
    {
        return $this->updatableOnNull;
    }

    public static function setTimestamps(array $fields): void
    {
        static::$timestamps = $fields;
    }

    public static function getTimestamps(): array
    {
        return static::$timestamps;
    }
}
