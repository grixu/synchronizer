<?php

namespace Grixu\Synchronizer\Engine;

use Grixu\Synchronizer\Engine\Abstracts\BaseEngine;
use Grixu\Synchronizer\Engine\Contracts\Transformer;

class Model extends BaseEngine
{
    public function sync(Transformer $transformer)
    {
        if ($this->input->count() <= 0) {
            return;
        }

        $checkRelations = $this->getAllRelations($transformer);

        $resetChecksum = count($checkRelations) > 0 && !empty($this->checksum);

        $transformed = $this->input->map(
            function ($item) use ($resetChecksum, $transformer) {
                if ($resetChecksum) {
                    $item[$this->checksum] = null;
                }

                return $transformer->sync($item);
            }
        );

        $fields = array_diff($transformer->getMap()->getModelFieldsArray(), [$this->modelKey]);

        $this->model::upsert(
            $transformed->toArray(),
            [$this->modelKey],
            $fields
        );

        $this->ids->push(...$this->input->pluck($this->key)->unique());
    }
}
