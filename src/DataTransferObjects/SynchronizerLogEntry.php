<?php

namespace Grixu\Synchronizer\DataTransferObjects;

use Spatie\DataTransferObject\DataTransferObject;

/**
 * Class SynchronizerLogEntry
 * @package Grixu\Synchronizer\DataTransferObjects
 */
class SynchronizerLogEntry extends DataTransferObject
{
    public string $localField;

    public string $foreignField;

    public ?string $localValue;

    public ?string $foreignValue;
}
