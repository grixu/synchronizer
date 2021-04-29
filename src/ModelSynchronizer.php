<?php

namespace Grixu\Synchronizer;

use Grixu\Synchronizer\Events\ModelCreatedEvent;
use Grixu\Synchronizer\Events\ModelSynchronizedEvent;
use Illuminate\Database\Eloquent\Model;
use Spatie\DataTransferObject\DataTransferObject;

class ModelSynchronizer
{
    protected Map $map;
    protected bool $isModelCreated = false;

    public function __construct(
        protected DataTransferObject|array $dto,
        protected Model|string $model,
        ?Map $map = null
    ) {
        $modelName = is_object($model) ? $model::class : $model;

        if (empty($map)) {
            if ($dto instanceof DataTransferObject) {
                $map = MapFactory::makeFromDto($dto, $modelName);
            } else {
                $map = MapFactory::makeFromArray($dto, $modelName);
            }
        }

        /** @var Map $map */
        $this->map = $map;
    }

    public function sync(): Model
    {
        if (is_string($this->model)) {
            $this->model = $this->createModel();
            $this->isModelCreated = true;

            event(new ModelCreatedEvent(get_class($this->model)));
        }

        $checksum = new Checksum($this->map, $this->model);

        if ($this->isModelCreated == true) {
            $checksum->update();
        }

        if ($checksum->validate()) {
            return $this->model;
        }

        $logger = new Logger(get_class($this->model), $this->model->id);

        $this->model->fill($this->makeData($this->model, $logger));
        $this->model->save();
        $checksum->update();
        $logger->save();

        event(new ModelSynchronizedEvent(get_class($this->model)));

        return $this->model;
    }

    protected function createModel(): Model
    {
        return $this->model::create($this->makeData());
    }

    protected function makeData(?Model $model = null, ?Logger $logger = null): array
    {
        $data = [];

        foreach ($this->map->get($model) as $entry) {
            $dtoFieldName = $entry->getDtoField();
            $modelFieldName = $entry->getModelField();

            $dtoField = ($this->dto instanceof DataTransferObject) ? $this->dto->$dtoFieldName : $this->dto[$dtoFieldName];

            $data[$modelFieldName] = $dtoField;

            optional($logger)->addChanges(
                $dtoFieldName,
                $modelFieldName,
                $dtoField,
                optional($model)->$modelFieldName
            );
        }

        return $data;
    }
}
