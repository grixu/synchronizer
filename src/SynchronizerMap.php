<?php

namespace Grixu\Synchronizer;

use Grixu\Synchronizer\Models\SynchronizerField;
use Illuminate\Support\Collection;

/**
 * Class SynchronizerMap
 * @package Grixu\Synchronizer
 */
class SynchronizerMap
{
    private string $model;

    private Collection $map;
    private Collection $excluded;
    protected Collection $excludedNullUpdate;
    private Collection $toSync;

    public function __construct(array $map, string $model)
    {
        $this->map = collect($map);
        $this->model = $model;

        // this is Collection of local field names which should be exclude from sync
        $excluded = $this->loadExcludedField();
        // this is Collection - map of excluded field
        $this->excluded = $this->map->intersectByKeys($excluded->toArray());

        $excluded = $this->loadExcludedFieldWithNullUpdate();
        $this->excludedNullUpdate = $this->map->intersectByKeys($excluded->toArray());

        $this->toSync = $this->map->diff($this->excluded);
    }

    protected function loadExcludedField(): Collection
    {
        return SynchronizerField::query()
            ->where('model', $this->model)
            ->pluck('id', 'field');
    }

    protected function loadExcludedFieldWithNullUpdate(): Collection
    {
        return SynchronizerField::query()
            ->where([
                ['model', '=', $this->model],
                ['update_empty', '=', true]
            ])
            ->pluck('id', 'field');
    }

    public function getExcludedNullUpdate(): Collection
    {
        return $this->excludedNullUpdate;
    }

    public function getExcluded(): Collection
    {
        return $this->excluded;
    }

    public function getToSync(): Collection
    {
        return $this->toSync;
    }

    public function getMap(): Collection
    {
        return $this->map;
    }

    public function getToMd5(): Collection
    {
        // Creating assoc array from fields marked in config as timestamps
        $arrCfg = [];
        foreach(config('synchronizer.log_turned_off_fields') as $val) {
            $arrCfg[$val] = 1;
        }

        // complete toSync field with null update enabled fields
        $map = $this->getToSync()->merge($this->getExcludedNullUpdate());
        // map of timestamps
        $ts = $map->intersectByKeys($arrCfg);

        return $map->diff($ts);
    }

    public function markAsExcluded(string $localField): void
    {
        $row = $this->toSync->search($localField);

        if ($row) {
            $takenOut = $this->toSync->pull($row);
            $this->excluded->push($takenOut);
        }
    }

    public function markToSync(string $localField): void
    {
        $row = $this->excluded->search($localField);

        if ($row) {
            $takenOut = $this->excluded->pull($row);
            $this->toSync->push($takenOut);
        }
    }
}
