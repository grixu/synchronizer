<?php

namespace Grixu\Synchronizer\Tests\Helpers;

use Illuminate\Support\Collection;

class TroubleLoader extends FakeLoader
{
    public static int $take = 0;

    public function getPiece(int $piece): Collection
    {
        if (self::$take === 0) {
            self::$take++;
            throw new \Exception('Fatal');
        }

        return parent::getPiece($piece);
    }
}
