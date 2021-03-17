<?php

namespace Grixu\Synchronizer;

use Grixu\Synchronizer\Exceptions\EmptyForeignKeyInDto;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log as LogFacade;

class CollectionSynchronizer
{
    protected Collection $dtoCollection;
    protected string $model;
    protected string $foreignKey;
    protected array $foreignKeys = [];

    protected int $created = 0;
    protected int $updated = 0;

    public function __construct(Collection $dtoCollection, string $model, string $foreignKey)
    {
        $this->dtoCollection = $dtoCollection;
        $this->model = $model;

        foreach ($dtoCollection as $dto) {
            if (!isset($dto->$foreignKey) || is_null($dto->$foreignKey)) {
                throw new EmptyForeignKeyInDto();
            }

            $this->foreignKeys[] = $dto->$foreignKey;
        }

        $this->foreignKey = $foreignKey;
    }

    public function sync(?array $map = null)
    {
        $map = $this->makeMap($map);

        $models = $this->loadModels();
        $idsNotFound = $this->diffCheck($models);
        $fk = $this->foreignKey;

        foreach ($this->dtoCollection as $dto) {
            if (in_array($dto->$fk, $idsNotFound)) {
                $model = (new ModelSynchronizer($dto, $this->model, $map))->sync();
                $this->created++;
            } else {
                $model = $models->where($fk, $dto->$fk)->first();
                (new ModelSynchronizer($dto, $model, $map))->sync();
                $this->updated++;
            }

            if (!empty($dto->relationships)) {
                $rs = new RelationshipSynchronizer($model);
                $rs->sync($dto->relationships);
            }
        }

        $this->sendReport();
    }

    protected function loadModels(): EloquentCollection
    {
        return $this->model::query()
            ->whereIn($this->foreignKey, $this->foreignKeys)
            ->get();
    }

    protected function diffCheck(EloquentCollection $models): array
    {
        $idsFound = $models->pluck($this->foreignKey)->toArray();
        return array_diff($this->foreignKeys, $idsFound);
    }

    protected function makeMap(?array $map): Map
    {
        if (is_array($map)) {
            return MapFactory::makeFromArray($map, $this->model);
        } else {
            return MapFactory::makeFromDto($this->dtoCollection->first(), $this->model);
        }
    }

    protected function sendReport(): void
    {
        if (config('synchronizer.send_slack_sum_up') == true && !empty(config('logging.channels.slack.url'))) {
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
