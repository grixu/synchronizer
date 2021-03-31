<?php

namespace Grixu\Synchronizer\Attributes;

use Attribute;

#[Attribute]
class SynchronizeWith
{
    public function __construct(public string $className)
    {
    }
}
