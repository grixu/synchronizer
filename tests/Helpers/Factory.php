<?php

namespace Grixu\Synchronizer\Tests\Helpers;

/**
 * Class Factory
 * @package Grixu\SyncLog\Tests\Helpers
 */
abstract class Factory
{
    abstract public function create(array $parameters = []);

    public static function new(): self
    {
        return new static();
    }

    public static function times(int $times): FactoryCollection
    {
        return new FactoryCollection(static::class, $times);
    }
}
