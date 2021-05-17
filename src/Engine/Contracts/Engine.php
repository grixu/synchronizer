<?php

namespace Grixu\Synchronizer\Engine\Contracts;

use Illuminate\Support\Collection;

interface Engine
{
    public function sync(Transformer $transformer);
    public function getIds(): Collection;
}
