<?php

namespace Grixu\Synchronizer;

use Exception;
use Grixu\Synchronizer\Config\SyncConfig;
use Grixu\Synchronizer\Engine\BelongsTo;
use Grixu\Synchronizer\Engine\BelongsToMany;
use Grixu\Synchronizer\Engine\ExcludedField;
use Grixu\Synchronizer\Engine\Model;
use Grixu\Synchronizer\Events\SynchronizerEvent;
use Illuminate\Support\Collection;

class Synchronizer
{
    protected Map $map;
    protected Transformer $transformer;
    protected Logger $logger;
    protected Collection $input;
    protected string $key;
    protected string $model;

    public function __construct(array $input, SyncConfig $syncConfig, string|null $batchId = 'none')
    {
        if (empty($input)) {
            throw new Exception('Empty data input');
        }

        $input = collect($input)->filter();

        $this->key = $syncConfig->getForeignKey();
        $this->model = $syncConfig->getLocalModel();

        $this->map = MapFactory::makeFromArray($input->first(), $syncConfig->getLocalModel());
        $this->transformer = new Transformer($this->map);

        $batchId = (empty($batchId)) ? 'none' : $batchId;
        $this->logger = new Logger($batchId, $this->model);

        $checksum = new Checksum($input, $this->key, $syncConfig->getLocalModel());
        $this->input = $checksum->get();
    }

    public function sync()
    {
        $belongsTo = new BelongsTo($this->input, $this->key, $this->model);
        $belongsTo->sync($this->transformer);

        $rest = $this->diffCompleted($belongsTo->getIds()->toArray());
        $model = new Model($rest, $this->key, $this->model);
        $model->sync($this->transformer);

        $belongsToMany = new BelongsToMany($this->input, $this->key, $this->model);
        $belongsToMany->sync();

        if (!empty($this->map->getUpdatableOnNullFields())) {
            $excludedField = new ExcludedField($this->input, $this->key, $this->model);
            $excludedField->sync($this->transformer);
        }

        $this->logger->log($belongsTo->getIds()->toArray(), Logger::BELONGS_TO);
        $this->logger->log($model->getIds()->toArray(), Logger::MODEL);
        $this->logger->log($model->getIds()->toArray(), Logger::BELONGS_TO_MANY);

        event(new SynchronizerEvent($this->model, $this->input->toArray()));
    }

    protected function diffCompleted(array $ids): Collection
    {
        $key = $this->key;

        return $this->input->filter(function ($item) use ($ids, $key) {
            return !in_array($item[$key], $ids);
        });
    }
}
