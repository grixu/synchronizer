<?php

namespace Grixu\Synchronizer;

use Grixu\Synchronizer\Events\SynchronizerDetectChangesEvent;
use Grixu\Synchronizer\Exceptions\EmptyMd5FieldNameInConfigException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Spatie\DataTransferObject\DataTransferObject;

/**
 * Class Synchronizer
 * @package Grixu\Synchronizer
 */
class Synchronizer
{
    protected SynchronizerMap $map;
    protected Model $local;
    protected DataTransferObject $foreign;
    protected SynchronizerLogger $logger;
    protected string $md5;

    /**
     * Synchronizer constructor.
     * @param array $map
     * @param Model $local
     * @param DataTransferObject $foreign
     */
    public function __construct(array $map, Model $local, DataTransferObject $foreign)
    {
        $this->map = new SynchronizerMap($map, get_class($local));
        $this->local = $local;
        $this->foreign = $foreign;

        $this->logger = new SynchronizerLogger(get_class($local), $local->getKey());
        $this->md5 = '';
    }

    public function syncExcludedButLocalEmpty()
    {
        foreach ($this->map->getExcludedNullUpdate() as $key => $value) {
            $this->logger->addChanges(
                $key,
                $value,
                $this->local->$key,
                $this->foreign->$value
            );

            if (!empty($this->foreign->$value) && empty($this->local->$key)) {
                $this->local->$key = $this->foreign->$value;
            }

        }
    }

    protected function generateMd5(Collection $collection): string
    {
        return md5(json_encode($collection->toArray()));
    }

    public function checkChanges(): bool
    {
        if (config('synchronizer.md5_control') == false || empty(config('synchronizer.md5_control'))) {
            return true;
        }

        $md5FieldName = config('synchronizer.md5_local_model_field');

        if (empty($md5FieldName)) {
            throw new EmptyMd5FieldNameInConfigException();
        }

        if (empty($this->local->$md5FieldName)) {
            return true;
        }

        $this->md5 = $this->generateMd5(
            collect($this->foreign->toArray())
            ->only($this->getMap()->getToMd5()->values())
        );

        return $this->local->$md5FieldName !== $this->md5;
    }

    public function sync(bool $empty = true)
    {
        if (!$this->checkChanges() && config('synchronizer.md5_control') == true) {
            return ;
        }

        foreach ($this->map->getToSync()->toArray() as $key => $value) {
            $this->logger->addChanges(
                $key,
                $value,
                $this->local->$key,
                $this->foreign->$value
            );

            $this->local->$key = $this->foreign->$value;
        }

        if ($empty == true) {
            $this->syncExcludedButLocalEmpty();
        }

        $md5Field = config('synchronizer.md5_local_model_field');
        if (config('synchronizer.md5_control') == true && !empty($md5Field)) {
            $this->local->$md5Field = $this->md5;

            event(new SynchronizerDetectChangesEvent($this->local));
        }

        $this->logger->save();
    }

    public function getMap(): SynchronizerMap
    {
        return $this->map;
    }

    public function getLocal(): Model
    {
        return $this->local;
    }

    public function getForeign(): DataTransferObject
    {
        return $this->foreign;
    }

    public function getLogger(): SynchronizerLogger
    {
        return $this->logger;
    }

    public function getMd5(): string
    {
        return $this->md5;
    }
}
