<?php

namespace Grixu\Synchronizer;

use Grixu\Synchronizer\DataTransferObjects\SynchronizerLogData;
use Grixu\Synchronizer\DataTransferObjects\SynchronizerLogEntryCollection;
use Grixu\Synchronizer\Models\SynchronizerLog;

/**
 * Class SynchronizerLogger
 * @package Grixu\Synchronizer
 */
class SynchronizerLogger
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

    public function addChanges(string $localField, string $foreignField, ?string $localValue, ?string $foreignValue): void
    {
        if ($localValue !== $foreignValue && !in_array($localField, config('synchronizer.log_turned_off_fields'))) {
            $this->changes[] =
                [
                    'localField' => $localField,
                    'foreignField' => $foreignField,
                    'localValue' => $localValue,
                    'foreignValue' => $foreignValue
                ];
        }
    }

    public function get()
    {
        return new SynchronizerLogData(
            [
                'model' => $this->model,
                'id' => $this->id,
                'changes' => SynchronizerLogEntryCollection::create($this->changes)
            ]
        );
    }

    public function save(): void
    {
        if (config('synchronizer.db_logging') == true) {
            SynchronizerLog::create(
                [
                    'model' => $this->model,
                    'model_id' => $this->id,
                    'log' => $this->get()
                ]
            );
        }
    }


    public function getChanges(): array
    {
        return $this->changes;
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
