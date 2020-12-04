<?php

namespace Grixu\Synchronizer;

use Illuminate\Database\Eloquent\Model;
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
    }

    public function syncExcludedButLocalEmpty()
    {
        foreach ($this->map->getExcludedNullUpdate() as $key => $value) {
            if (!empty($this->foreign->$value) && empty($this->local->$key)) {
                $this->local->$key = $this->foreign->$value;
            }

            $this->logger->addChanges(
                $key,
                $value,
                $this->local->$key,
                $this->foreign->$value
            );
        }
    }


    public function sync(bool $empty = true)
    {
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


}
