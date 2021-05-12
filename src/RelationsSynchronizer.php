<?php

namespace Grixu\Synchronizer;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;

class RelationsSynchronizer
{
    protected Collection $belongsTo;
    protected Collection $belongsToMany;
    protected Collection $loadedBelongedTo;
    protected Collection $loadedBelongedToMany;
    protected Model $localModel;
    protected Collection $attachedIds;
    protected Collection $syncedIds;

    public function __construct(string $localModel, Collection $dataSet)
    {
        $this->attachedIds = collect();
        $this->syncedIds = collect();
        $this->localModel = new $localModel;

        $this->testRelations($dataSet);

        $this->belongsTo = $dataSet->filter(
            function ($item) {
                return array_filter($item['relations'], fn($item) => $item['type'] === BelongsTo::class);
            }
        );
        $this->belongsToMany = $dataSet->filter(
            function ($item) {
                return array_filter($item['relations'], fn($item) => $item['type'] === BelongsToMany::class);
            }
        );

        $this->loadedBelongedTo = $this->belongsTo->count() > 0 ? $this->loadRelated($this->belongsTo) : collect();
        $this->loadedBelongedToMany = $this->belongsToMany->count() > 0 ? $this->loadRelated(
            $this->belongsToMany
        ) : collect();
    }

    protected function testRelations(Collection $input)
    {
        $test = $this->localModel;

        $input->pluck('relations')
            ->flatten(1)
            ->filter()
            ->pluck('relation', 'foreignClass')
            ->unique()
            ->each(
                function ($relation, $class) use ($test) {
                    if (!$test->$relation() instanceof Relation) {
                        throw new Exception($relation . ' in ' . $this->localModel::class . ' is not relation');
                    }

                    if (!$test->$relation()->getRelated() instanceof $class) {
                        throw new Exception(
                            $relation . ' in ' . $this->localModel::class . ' is not related with ' . $class
                        );
                    }
                }
            );
    }

    protected function loadRelated(Collection $input): Collection
    {
        $loaded = collect();

        $input->pluck('relations')
            ->flatten(1)
            ->filter()
            ->groupBy('relation')
            ->filter()
            ->each(
                function ($collection, $relation) use ($loaded) {

                    $data = collect();
                    $model = $this->localModel->$relation()->getRelated();

                    $collection->groupBy('foreignField')
                        ->filter()
                        ->each(
                            function ($collection, $foreignField) use ($data, $relation, $model) {
                                $foreignKeys = $collection->pluck('foreignKeys')->flatten(1);

                                $data->put(
                                    $foreignField,
                                    $model::query()
                                        ->whereIn($foreignField, $foreignKeys)
                                        ->pluck('id', $foreignField)
                                );
                            }
                        );

                    $loaded->put($relation, $data);
                }
            );

        return $loaded;
    }

    public function syncBelongsToMany()
    {
        $collectionToSync = collect();
        $this->belongsToMany->groupBy('relations.*.relation')
            ->each(
                function ($collection, $relation) use ($collectionToSync) {
                    /** @var Collection $collection */
                    $collection->each(
                        function ($item) use ($relation, $collectionToSync) {

                            foreach ($item['relations'] as $rel) {
                                if (empty($rel['foreignKeys']) && $rel['type'] !== BelongsToMany::class) {
                                    continue;
                                }

                                $relatedIds = [];

                                foreach ($rel['foreignKeys'] as $key) {
                                    $relatedIds[] = $this->loadedBelongedToMany[$relation][$rel['foreignField']][$key];
                                }

                                $collectionToSync->put($item['xlId'], [$relation => $relatedIds]);
                            }
                        }
                    );
                }
            );

        $models = $this->localModel::query()
            ->whereIn('xl_id', $collectionToSync->keys())
            ->select('id', 'xl_id')
            ->get();

        foreach ($models as $model) {
            $relations = $collectionToSync[$model->xl_id];

            foreach ($relations as $relation => $fks) {
                $model->$relation()->sync($fks);
            }
        }

        $this->syncedIds->push(...$collectionToSync->keys());
    }


    public function syncBelongsTo(): void
    {
        $upsert = collect();
        $upsertFieldNames = collect();

        // TODO: Dodać wyłączenie jeśli loadedBelongedTo jest pusty lub dla danej relacji jest pusty lub dla danego klucza

        $this->belongsTo->groupBy('relations.*.relation')
            ->each(
                function ($collection, $relation) use ($upsert, $upsertFieldNames) {
                    $fieldName = $this->localModel->$relation()->getForeignKeyName();
                    $upsertFieldNames->push($fieldName);

                    /** @var Collection $collection */
                    $collection->each(
                        function ($item) use ($fieldName, $relation, $upsert) {
                            foreach ($item['relations'] as $rel) {
                                if (empty($rel['foreignKeys']) || $rel['type'] !== BelongsTo::class) {
                                    continue;
                                }

                                $relatedId = $this->loadedBelongedTo[$relation][$rel['foreignField']][$rel['foreignKeys']];

                                $upsert->push(
                                    [
                                        'xl_id' => $item['xlId'],
                                        $fieldName => $relatedId
                                    ]
                                );
                            }
                        }
                    );
                }
            );

        $this->attachedIds->push(...$upsert->pluck('xl_id'));
        $this->localModel::upsert($upsert->toArray(), ['xl_id'], $upsertFieldNames->unique()->toArray());
    }

    public function getAttachedIds(): Collection
    {
        return $this->attachedIds;
    }

    public function getSyncedIds(): Collection
    {
        return $this->syncedIds;
    }
}
