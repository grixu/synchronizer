<?php

namespace Grixu\Synchronizer;

use Grixu\Synchronizer\Models\ExcludedField;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Map
{
    protected Collection $map;

    public function __construct(array $map, string $model)
    {
        $this->map = collect();

        foreach ($map as $dtoField => $modelField) {
            $excludedField = $this->getExcludedField($model, $modelField);

            $this->map->push(
                new MapEntry($dtoField, $modelField, $excludedField)
            );
        }
    }

    protected function getExcludedField(string $model, string $field): ExcludedField | null
    {
        return ExcludedField::query()
            ->where(
                [
                    ['model', '=', $model],
                    ['field', '=', $field]
                ]
            )
            ->firstOr(fn() => null);
    }

    public function get(?Model $model = null): Collection
    {
        return $this->map->filter(function ($item) use ($model) {
            $field = $item->getModelField();
            return $item->isSyncable(optional($model)->$field);
        });
    }

    public function getWithoutTimestamps(?Model $model = null): Collection
    {
        return $this->map->filter(function ($item) use ($model) {
            $field = $item->getModelField();
            return $item->isSyncable(optional($model)->$field) && !$item->isTimestamp();
        });
    }

    public function getModelFieldsArray(?Model $model=null): array
    {
        if (config('synchronizer.checksum.timestamps_excluded')) {
            $fields = $this->getWithoutTimestamps($model);
        } else {
            $fields = $this->get($model);
        }

        return $fields->map(fn ($item) => $item->getModelField())->toArray();
    }
}
