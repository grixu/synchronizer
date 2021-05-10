<?php

namespace Grixu\Synchronizer;

use Grixu\Synchronizer\Models\ExcludedField;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Map
{
    protected Collection $map;
    protected Collection $mapWithoutTimestamps;
    protected array $updatableOnNull = [];
    protected static array $timestamps;

    public function __construct(array $fields, protected string $model)
    {
        $this->map = collect();
        $this->mapWithoutTimestamps = collect();
        $excludedFields = $this->getExcludedFields($model);

        foreach ($fields as $field) {
            $modelField = Str::snake($field);

            $excludedField = $excludedFields->where('field', $modelField)->first();

            if ($excludedField) {
                if ($excludedField->update_empty) {
                    $this->updatableOnNull[] = $modelField;
                }

                continue;
            }

            $this->map->put($field, $modelField);

            if (!in_array($modelField, static::$timestamps)) {
                $this->mapWithoutTimestamps->put($field, $modelField);
            }
        }
    }

    protected function getExcludedFields(string $model): Collection
    {
        return ExcludedField::query()
            ->where('model', $model)
            ->get();
    }

    public function get(): Collection
    {
        return $this->map;
    }

    public function getWithoutTimestamps(): Collection
    {
        return $this->mapWithoutTimestamps;
    }

    public function getModelFieldsArray(): array
    {
        return $this->map->values()->toArray();
    }

    public function getUpdatableOnNullFields(): array
    {
        return $this->updatableOnNull;
    }

    public static function setTimestamps(array $fields): void
    {
        static::$timestamps = $fields;
    }
}
