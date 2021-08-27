<?php

namespace Grixu\Synchronizer\Engine\Map;

use Grixu\Synchronizer\Config\Contracts\EngineConfigInterface;
use Grixu\Synchronizer\Engine\Contracts\Map as MapInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Map implements MapInterface
{
    protected array $map = [];
    protected array $mapWithoutTimestamps = [];
    protected array $updatableOnNull = [];
    protected Collection $excludedFields;
    protected EngineConfigInterface $config;

    public function __construct(array $fields)
    {
        $this->config = app(EngineConfigInterface::class);
        $this->excludedFields = collect();
        $this->transformExcludedField();

        if (!empty($this->config->getChecksumField())) {
            $fields[] = $this->config->getChecksumField();
        }

        $fields = array_diff($fields, ['relations']);

        foreach ($fields as $field) {
            $this->add($field);
        }
    }

    private function transformExcludedField()
    {
        foreach ($this->config->getExcludedFields() as $key => $value) {
            if (is_array($value)) {
                $this->excludedFields->put(
                    Str::snake($key),
                    new ExcludedField($key, $value['nullable'] ?? true)
                );
            } else {
                $this->excludedFields->put(
                    Str::snake($value),
                    new ExcludedField($value)
                );
            }
        }
    }


    public function add(string $field, string|null $modelField = null): void
    {
        $modelField = (empty($modelField)) ? Str::snake($field) : $modelField;
        if (in_array($modelField, $this->map)) {
            return;
        }

        /** @var ExcludedField $excludedField */
        $excludedField = $this->excludedFields->get($modelField);

        if ($excludedField) {
            if ($excludedField->isFillable()) {
                $this->updatableOnNull[] = $modelField;
            }

            return;
        }

        $this->map[$field] = $modelField;

        if (!in_array($modelField, $this->config->getTimestamps())) {
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
