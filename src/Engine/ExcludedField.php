<?php

namespace Grixu\Synchronizer\Engine;

use Grixu\Synchronizer\Engine\Abstracts\BaseEngine;
use Grixu\Synchronizer\Engine\Contracts\Transformer;

class ExcludedField extends BaseEngine
{
    public function sync(Transformer $transformer)
    {
        $allIds = $this->input->pluck($this->key);
        $updatable = $transformer->getMap()->getUpdatableOnNullFields();

        if (!empty($updatable)) {
            foreach ($updatable as $dtoField => $modelField) {
                $data = $this->model::query()
                    ->whereIn($this->modelKey, $allIds)
                    ->whereNull($modelField)
                    ->get();

                $data = $data->map(function ($item) use ($dtoField, $modelField) {
                    $inputValue = $this->input->where($this->key, $item->{$this->modelKey})->first();

                    $item->{$dtoField} = $inputValue['fillable'][$modelField];

                    return $item;
                });

                if ($data->count() > 0) {
                    $this->model::upsert($data->toArray(), [$this->modelKey], [$modelField]);

                    $this->ids->push($data->pluck($this->modelKey));
                }
            }
        }
    }
}
