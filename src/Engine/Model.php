<?php

namespace Grixu\Synchronizer\Engine;

use Grixu\Synchronizer\Abstracts\BaseEngine;
use Grixu\Synchronizer\Transformer;

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

        $fields = array_diff($transformer->getMap()->getModelFieldsArray(), [$this->modelKey]);

        $this->model::upsert(
            $transformed->toArray(),
            [$this->modelKey],
            $fields
        );

        $this->ids->push(...$this->input->pluck($this->key));
    }
}
