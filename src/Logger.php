<?php

namespace Grixu\Synchronizer;

use Grixu\Synchronizer\Models\Log;

class Logger
{
    protected array $changes;

    protected string $model;
    protected int $id;

    public function __construct(string $model, int $id)
    {
        $this->model = $model;
        $this->id = $id;
        $this->changes = [];
    }

    public function addChanges(string $dtoField, string $modelField, $dtoValue = null, $modelValue = null): void
    {
        if ($dtoValue !== $modelValue && !in_array($modelField, config('synchronizer.sync.timestamps'))) {
            $this->changes[] =
                [
                    'dtoField' => $dtoField,
                    'modelField' => $modelField,
                    'dtoValue' => $dtoValue,
                    'modelValue' => $modelValue
                ];
        }
    }

    public function get(): array
    {
        return $this->changes;
    }

    public function save(): void
    {
        if (config('synchronizer.sync.logging') == true && count($this->changes) > 0) {
            Log::create(
                [
                    'model' => $this->model,
                    'model_id' => $this->id,
                    'log' => $this->changes,
                ]
            );
        }
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
