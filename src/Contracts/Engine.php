<?php

namespace Grixu\Synchronizer\Contracts;

use Grixu\Synchronizer\Transformer;
use Illuminate\Support\Collection;

interface Engine
{
    public function sync(Transformer|null $transformer = null);
    public function getIds(): Collection;
}
