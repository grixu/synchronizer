<?php

namespace Grixu\Synchronizer;

use Exception;
use Grixu\Synchronizer\Engine\BelongsTo;
use Grixu\Synchronizer\Engine\BelongsToMany;
use Grixu\Synchronizer\Engine\Contracts\EngineConfigInterface;
use Grixu\Synchronizer\Engine\Events\SynchronizerEvent;
use Grixu\Synchronizer\Engine\ExcludedField;
use Grixu\Synchronizer\Engine\Map\Map;
use Grixu\Synchronizer\Engine\Map\MapFactory;
use Grixu\Synchronizer\Engine\Model;
use Grixu\Synchronizer\Engine\Transformer\Transformer;
use Grixu\Synchronizer\Logs\Logger;
use Illuminate\Support\Collection;

class Synchronizer
{
    protected Map $map;
    protected Transformer $transformer;
    protected Logger $logger;
    protected Collection $input;

    protected EngineConfigInterface $config;

    public function __construct(array $input, string|null $batchId = 'none')
    {
        if (empty($input)) {
            throw new Exception('Empty data input');
        }
        $input = collect($input)->filter();

        $this->config = app(EngineConfigInterface::class);
        $this->map = MapFactory::makeFromArray($input->first());
        $this->transformer = new Transformer($this->map);

        $batchId = (empty($batchId)) ? 'none' : $batchId;
        $this->logger = new Logger($batchId, $this->config->getModel());

        if (!empty($this->config->getChecksumField()) && config('synchronizer.checksum.control')) {
            $checksum = new Checksum($input, $this->config);
            $this->input = $checksum->get();
        } else {
            $this->input = $input;
        }
    }

    public function sync()
    {
        if ($this->input->count() <= 0) {
            return;
        }

        $belongsTo = new BelongsTo($this->config, $this->input);
        $belongsTo->sync($this->transformer);

        $this->logger->log($belongsTo->getIds()->toArray(), Logger::BELONGS_TO);

        $rest = $this->diffCompleted($belongsTo->getIds()->toArray());

        if ($rest->count() > 0) {
            $model = new Model($this->config, $rest);
            $model->sync($this->transformer);
            $this->logger->log($model->getIds()->toArray(), Logger::MODEL);

            $belongsToMany = new BelongsToMany($this->config, $this->input);
            $belongsToMany->sync($this->transformer);
            $this->logger->log($belongsToMany->getIds()->toArray(), Logger::BELONGS_TO_MANY);
        }

        if (!empty($this->map->getUpdatableOnNullFields())) {
            $excludedField = new ExcludedField($this->config, $this->input);
            $excludedField->sync($this->transformer);
            $this->logger->log($excludedField->getIds()->toArray(), Logger::EXCLUDED_FIELDS);
        }

        event(
            new SynchronizerEvent(
                $this->config->getModel(),
                $this->config->getChecksumField(),
                $this->input->toArray()
            )
        );
    }

    protected function diffCompleted(array $ids): Collection
    {
        $key = $this->config->getKey();

        return $this->input->filter(function ($item) use ($ids, $key) {
            return !in_array($item[$key], $ids);
        });
    }
}
