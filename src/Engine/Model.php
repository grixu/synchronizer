<?php

namespace Grixu\Synchronizer\Engine;

use Grixu\Synchronizer\Abstracts\BaseEngine;
use Grixu\Synchronizer\Transformer;
use Illuminate\Support\Str;

class Model extends BaseEngine
{
    public function sync(?Transformer $transformer = null)
    {
        if ($this->input->count() <= 0) {
            return;
        }

        $transformed = $this->input->map(function ($item) use ($transformer) {
            return $transformer->sync($item);
        });

        $modelKey = Str::snake($this->key);
        $fields = array_diff($transformer->getMap()->getModelFieldsArray(), [$modelKey]);

        $this->model::upsert(
            $transformed->toArray(),
            [$modelKey],
            $fields
        );

        $this->ids->push(...$this->input->pluck($this->key));
    }
}
