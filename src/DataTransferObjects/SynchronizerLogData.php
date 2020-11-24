<?php

namespace Grixu\Synchronizer\DataTransferObjects;

use JessArcher\CastableDataTransferObject\CastableDataTransferObject;

/**
 * Class SynchronizerLogData
 * @package Grixu\Synchronizer\DataTransferObjects
 */
class SynchronizerLogData extends CastableDataTransferObject
{
    public string $model;

    public int $id;

    public SynchronizerLogEntryCollection $changes;
}
