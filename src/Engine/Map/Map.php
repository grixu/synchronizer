<?php

namespace Grixu\Synchronizer\Engine\Map;

use Grixu\Synchronizer\Checksum;
use Grixu\Synchronizer\Engine\Contracts\Map as MapInterface;
use Grixu\Synchronizer\Models\ExcludedField;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Map implements MapInterface
{
    protected array $map = [];
    protected array $mapWithoutTimestamps = [];
    protected array $updatableOnNull = [];
    protected Collection $excludedFields;
    protected static array $timestamps;

    public function __construct(array $fields, protected string $model)
    {
        $this->excludedFields = $this->getExcludedFields($model);

        if (!empty(Checksum::$checksumField)) {
            $fields[] = Checksum::$checksumField;
        }

        $fields = array_diff($fields, ['relations']);

        foreach ($fields as $field) {
            $this->add($field);
        }
    }

    protected function getExcludedFields(string $model): Collection
    {
        return ExcludedField::query()
            ->where('model', $model)
            ->get();
    }

    public function add(string $field, string|null $modelField = null): void
    {
        $modelField = (empty($modelField)) ? Str::snake($field) : $modelField;
        if (in_array($modelField, $this->map)) {
            return;
        }

        $excludedField = $this->excludedFields->where('field', $modelField)->first();

        if ($excludedField) {
            if ($excludedField->update_empty) {
                $this->updatableOnNull[] = $modelField;
            }

            return;
        }

        $this->map[$field] = $modelField;

        if (!in_array($modelField, static::$timestamps)) {
            $this->mapWithoutTimestamps[$field] = $modelField;
        }
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
