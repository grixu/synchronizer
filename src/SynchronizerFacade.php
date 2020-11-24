<?php

namespace Grixu\Synchronizer;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Grixu\Synchronizer\SynchronizerFactory
 * @method static make(string[] $map, \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model $local, Tests\Helpers\ProductData $foreign)
 */
class SynchronizerFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'synchronizer';
    }
}
