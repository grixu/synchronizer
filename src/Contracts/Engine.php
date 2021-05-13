<?php

namespace Grixu\Synchronizer\Contracts;

use Illuminate\Support\Collection;

interface Engine
{
    public function sync();
    public function getIds(): Collection;
}
