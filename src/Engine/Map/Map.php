<?php

namespace Grixu\Synchronizer\Engine\Map;

use Grixu\Synchronizer\Engine\Contracts\EngineConfigInterface;
use Grixu\Synchronizer\Engine\Contracts\Map as MapInterface;
use Illuminate\Support\Str;

class Map implements MapInterface
{
    protected array $map = [];
    protected array $mapWithoutTimestamps = [];
    protected array $updatableOnNull = [];
    protected array $excludedFields;
    protected EngineConfigInterface $config;

    public function __construct(array $fields)
    {
        $this->config = app(EngineConfigInterface::class);
        $this->excludedFields = array_map(fn ($item) => Str::snake($item), $this->config->getExcluded());
        $this->loadUpdatable();

        if (!empty($this->config->getChecksumField())) {
            $fields[] = $this->config->getChecksumField();
        }

        $fields = array_diff($fields, ['relations', 'fillable']);

        foreach ($fields as $field) {
            $this->add($field);
        }
    }

    private function loadUpdatable()
    {
        foreach ($this->config->getFillable() as $item) {
            $this->updatableOnNull[$item] = Str::snake($item);
        }
    }

    public function add(string $field, string|null $modelField = null): void
    {
        $modelField = (empty($modelField)) ? Str::snake($field) : $modelField;

        if (in_array($modelField, $this->map)) {
            return;
        }

        if (in_array($field, $this->config->getExcluded())) {
            return;
        }

        if (in_array($field, $this->config->getFillable())) {
            return;
        }

        $this->map[$field] = $modelField;

        if (!in_array($field, $this->config->getTimestamps())) {
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
}
