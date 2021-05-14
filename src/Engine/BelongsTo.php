<?php

namespace Grixu\Synchronizer\Engine;

use Grixu\Synchronizer\Abstracts\RelationEngine;
use Grixu\Synchronizer\Transformer;
use Illuminate\Database\Eloquent\Relations\BelongsTo as BelongsToRelation;
use Illuminate\Support\Collection;

class BelongsTo extends RelationEngine
{
    protected function filter(Collection $dataSet): Collection
    {
        return $dataSet->filter(
            function ($item) {
                if (!isset($item['relations']) || empty($item['relations'])) {
                    return false;
                }

                return array_filter($item['relations'], fn($item) => $item['type'] === BelongsToRelation::class);
            }
        );
    }

    public function sync(Transformer|null $transformer = null)
    {
        if ($this->loaded->flatten()->count() <= 0) {
            return;
        }

        $upsert = collect();
        $upsertFieldNames = collect();

        $this->input->groupBy('relations.*.relation')
            ->filter()
            ->each(
                function ($collection, $relation) use ($upsert, $upsertFieldNames, $transformer) {
                    $fieldName = $this->model->$relation()->getForeignKeyName();
                    $upsertFieldNames->push($fieldName);

                    /** @var Collection $collection */
                    $collection->each(
                        function ($item) use ($fieldName, $relation, $upsert, $transformer) {
                            foreach ($item['relations'] as $rel) {
                                if (empty($rel['foreignKeys']) || $rel['type'] !== BelongsToRelation::class) {
                                    continue;
                                }

                                $relatedId = $this->loaded[$relation][$rel['foreignField']][$rel['foreignKeys']];

                                $transformed = $transformer->sync($item, [$fieldName => $relatedId]);
                                $upsert->push($transformed);
                            }
                        }
                    );
                }
            );

        $fields = array_merge($upsertFieldNames->unique()->toArray(), $transformer->getMap()->getModelFieldsArray());
        $this->model::upsert($upsert->toArray(), [$this->modelKey], $fields);

        $this->ids->push(...$upsert->pluck($this->modelKey));
    }
}
