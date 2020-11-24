<?php

namespace Grixu\Synchronizer\DataTransferObjects;

use Spatie\DataTransferObject\DataTransferObjectCollection;

/**
 * Class SynchronizerLogEntryCollection
 * @package Grixu\Synchronizer\DataTransferObjects
 */
class SynchronizerLogEntryCollection extends DataTransferObjectCollection
{
    public static function create(array $data): SynchronizerLogEntryCollection
    {
        return new static(SynchronizerLogEntry::arrayOf($data));
    }

    public function current(): SynchronizerLogEntry
    {
        return parent::current();
    }
}
