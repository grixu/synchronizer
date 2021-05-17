<?php

namespace Grixu\Synchronizer\Engine\Contracts;

interface Transformer
{
    public function sync(array $data, array $additional = []): array;
    public function getMap(): Map;
}
