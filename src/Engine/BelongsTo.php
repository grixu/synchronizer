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

        $allRelations = [];
        $this->input->pluck('relations.*.relation')
            ->flatten()
            ->filter(function ($relation) {
                if ($this->model->$relation() instanceof BelongsToRelation) {
                    return true;
                }

                return false;
            })
            ->each(function ($relation) use ($transformer, &$allRelations) {
                $fieldName = $this->model->$relation()->getForeignKeyName();
                $transformer->getMap()->add($fieldName);
                $allRelations[$relation] = $fieldName;
            })
            ->toArray();

        $upsert = $this->input->map(
            function ($item) use ($transformer, $allRelations) {
                $relatedFields = [];

                foreach($item['relations'] as $rel) {
                    if (empty($allRelations[$rel['relation']]) || empty($rel['foreignKeys'])) {
                        continue;
                    }

                    if (!isset($this->loaded[$rel['relation']][$rel['foreignField']][$rel['foreignKeys']])) {
                        continue;
                    }

                    $fieldName = $allRelations[$rel['relation']];
                    $relatedFields[$fieldName] = $this->loaded[$rel['relation']][$rel['foreignField']][$rel['foreignKeys']];
                }

                return $transformer->sync($item, $relatedFields);
            }
        );

        $this->model::upsert($upsert->toArray(), [$this->modelKey], $transformer->getMap()->getModelFieldsArray());

        $this->ids->push(...$upsert->pluck($this->modelKey)->unique());
    }
}
