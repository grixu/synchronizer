<?php

namespace Grixu\Synchronizer;

use Illuminate\Database\Eloquent\Model;
use Spatie\DataTransferObject\DataTransferObject;

/**
 * Class SynchronizerFactory
 * @package Grixu\Synchronizer
 */
class SynchronizerFactory
{
    public function make(array $map, Model $local, DataTransferObject $foreign)
    {
        return new Synchronizer($map, $local, $foreign);
    }
}
