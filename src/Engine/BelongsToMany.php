<?php

namespace Grixu\Synchronizer\Engine;

use Grixu\Synchronizer\Engine\Abstracts\RelationEngine;
use Grixu\Synchronizer\Engine\Contracts\Transformer;
use Illuminate\Database\Eloquent\Relations\BelongsToMany as BelongsToManyRelation;
use Illuminate\Support\Collection;

class BelongsToMany extends RelationEngine
{
    protected function filter(Collection $dataSet): Collection
    {
        return $dataSet->filter(
            function ($item) {
                if (empty($item['relations'])) {
                    return false;
                }

                return array_filter($item['relations'], fn ($item) => $item['type'] === BelongsToManyRelation::class);
            }
        );
    }

    public function sync(Transformer $transformer)
    {
        $collectionToSync = collect();
        $this->input->groupBy('relations.*.relation')
            ->each(
                function ($collection, $relation) use ($collectionToSync) {
                    if (!$this->model->{$relation}() instanceof BelongsToManyRelation) {
                        return;
                    }

                    /** @var Collection $collection */
                    $collection->each(
                        function ($item) use ($relation, $collectionToSync) {
                            foreach ($item['relations'] as $rel) {
                                if (empty($rel['foreignKeys']) || !is_array($rel['foreignKeys']) || $rel['type'] !== BelongsToManyRelation::class) {
                                    continue;
                                }

                                $relatedIds = [];

                                foreach ($rel['foreignKeys'] as $key) {
                                    if (isset($this->loaded[$relation][$rel['foreignField']][$key])) {
                                        $relatedIds[] = $this->loaded[$relation][$rel['foreignField']][$key];
                                    }
                                }

                                $collectionToSync->put($item['xlId'], [$relation => $relatedIds]);
                            }
                        }
                    );
                }
            );

        $models = $this->model::query()
            ->whereIn('xl_id', $collectionToSync->keys())
            ->select('id', 'xl_id')
            ->get();

        foreach ($models as $model) {
            $relations = $collectionToSync[$model->xl_id];

            foreach ($relations as $relation => $fks) {
                $model->{$relation}()->sync($fks);
            }
        }

        $this->ids->push(...$collectionToSync->keys());
    }
}
