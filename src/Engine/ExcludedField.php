<?php

namespace Grixu\Synchronizer\Engine;

use Grixu\Synchronizer\Abstracts\BaseEngine;
use Grixu\Synchronizer\Transformer;
use Illuminate\Support\Str;

class ExcludedField extends BaseEngine
{
    public function sync(?Transformer $transformer = null)
    {
        $allIds = $this->input->pluck($this->key);
        $updatable = $transformer->getMap()->getUpdatableOnNullFields();

        if (!empty($updatable)) {
            foreach ($updatable as $field) {
                $data = $this->model::query()
                    ->whereIn($this->modelKey, $allIds)
                    ->whereNull($field)
                    ->get();

                $data = $data->map(function ($item) use ($field) {
                    $inputValue = $this->input->where($this->key, $item->{$this->modelKey})->first();

                    $item->{$field} = $inputValue[Str::snake($field)];

                    return $item;
                });

                if ($data->count() > 0) {
                    $this->model::upsert($data->toArray(), [$this->modelKey], [$field]);

                    $this->ids->push($data->pluck($this->modelKey));
                }
            }
        }
    }
}
