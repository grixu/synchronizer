<?php

namespace Grixu\Synchronizer\Engine;

use Grixu\Synchronizer\Abstracts\RelationEngine;
use Illuminate\Database\Eloquent\Relations\BelongsTo as BelongsToRelation;
use Illuminate\Support\Collection;

class BelongsTo extends RelationEngine
{
    protected function filter(Collection $dataSet): Collection
    {
        return $dataSet->filter(
            function ($item) {
                return array_filter($item['relations'], fn($item) => $item['type'] === BelongsToRelation::class);
            }
        );
    }

    public function sync()
    {
        if (empty($this->loaded)) {
            return;
        }

        $upsert = collect();
        $upsertFieldNames = collect();

        $this->input->groupBy('relations.*.relation')
            ->filter()
            ->each(
                function ($collection, $relation) use ($upsert, $upsertFieldNames) {
                    $fieldName = $this->model->$relation()->getForeignKeyName();
                    $upsertFieldNames->push($fieldName);

                    /** @var Collection $collection */
                    $collection->each(
                        function ($item) use ($fieldName, $relation, $upsert) {
                            foreach ($item['relations'] as $rel) {
                                if (empty($rel['foreignKeys']) || $rel['type'] !== BelongsToRelation::class) {
                                    continue;
                                }

                                $relatedId = $this->loaded[$relation][$rel['foreignField']][$rel['foreignKeys']];

                                $item[$fieldName] = $relatedId;
                                unset($item['relations']);
                                $upsert->push($item);
                            }
                        }
                    );
                }
            );

        $this->ids->push(...$upsert->pluck('xl_id'));
        ray($upsert);
        $this->model::upsert($upsert->toArray(), ['xl_id'], $upsertFieldNames->unique()->toArray());
    }
}
