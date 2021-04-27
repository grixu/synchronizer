<?php

namespace Grixu\Synchronizer;

use Closure;
use Exception;
use Grixu\Synchronizer\Exceptions\EmptyForeignKeyInDto;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Queue\SerializableClosure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log as LogFacade;
use Illuminate\Support\Str;
use Spatie\DataTransferObject\DataTransferObject;

class CollectionSynchronizer
{
    protected array $foreignKeys = [];

    protected int $created = 0;
    protected int $updated = 0;

    public function __construct(
        protected Collection $dtoCollection,
        protected string $model,
        protected string $foreignKey,
        protected SerializableClosure|Closure|null $errorHandler = null
    ) {
        foreach ($dtoCollection as $dto) {
            $this->checkForeignKey($dto, $foreignKey);

            $this->foreignKeys[] = ($dto instanceof DataTransferObject) ? $dto->$foreignKey : $dto[$foreignKey];
        }

        $this->checkIsCollectionNotEmpty();
        $this->dtoCollection = $this->dtoCollection->filter();
    }

    protected function checkForeignKey($dto, $foreignKey)
    {
        if ($dto instanceof DataTransferObject) {
            if (!isset($dto->$foreignKey) || is_null($dto->$foreignKey)) {
                throw new EmptyForeignKeyInDto();
            }
        }

        if (is_array($dto)) {
            if (!isset($dto[$foreignKey]) || is_null($dto[$foreignKey])) {
                throw new EmptyForeignKeyInDto();
            }
        }

        if (!is_array($dto) && !$dto instanceof DataTransferObject) {
            throw new Exception('Inappropriate data format');
        }
    }

    protected function checkIsCollectionNotEmpty(): void
    {
        if ($this->dtoCollection->count() <= 0) {
            throw new Exception('Empty Collection, nothing to sync');
        }
    }

    public function sync(?array $map = null)
    {
        $map = $this->makeMap($map);

        $models = $this->loadModels();
        $idsNotFound = $this->diffCheck($models);
        $fk = $this->foreignKey;

        foreach ($this->dtoCollection as $dto) {
            $dtoFk = ($dto instanceof DataTransferObject) ? $dto->$fk : $dto[$fk];

            if (in_array($dtoFk, $idsNotFound)) {
                $model = (new ModelSynchronizer($dto, $this->model, $map))->sync();
                $this->created++;
            } else {
                $model = $models->where(Str::snake($fk), $dtoFk)->first();
                (new ModelSynchronizer($dto, $model, $map))->sync();
                $this->updated++;
            }

            $dtoRelationships = null;
            if ($dto instanceof DataTransferObject) {
                $dtoRelationships = $dto->relationships;
            } else {
                if (isset($dto['relationships'])) {
                    $dtoRelationships = $dto['relationships'];
                }
            }

            if (!empty($dtoRelationships)) {
                $rs = new RelationshipSynchronizer($model);
                $rs->sync($dtoRelationships, $this->errorHandler);
            }
        }

        $this->sendReport();
    }

    protected function loadModels(): EloquentCollection
    {
        return $this->model::query()
            ->whereIn(Str::snake($this->foreignKey), $this->foreignKeys)
            ->get();
    }

    protected function diffCheck(EloquentCollection $models): array
    {
        $idsFound = $models->pluck(Str::snake($this->foreignKey))->toArray();
        return array_diff($this->foreignKeys, $idsFound);
    }

    protected function makeMap(?array $map): Map
    {
        if (is_array($map)) {
            return MapFactory::make($map, $this->model);
        }

        $element = $this->dtoCollection->first();

        if ($element instanceof DataTransferObject) {
            return MapFactory::makeFromDto($element, $this->model);
        }

        return MapFactory::makeFromArray($element, $this->model);
    }

    protected function sendReport(): void
    {
        if (!empty(config('logging.channels.slack.url'))) {
            LogFacade::channel('slack')
                ->notice(
                    sprintf(
                        "Synchronizacja modelu %s. Nowych obiektów: %d. Zaktualizowanych obiektów: %d",
                        $this->model,
                        $this->created,
                        $this->updated
                    )
                );
        }
    }
}
