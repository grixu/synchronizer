<?php

namespace Grixu\Synchronizer\Abstracts;

use Grixu\Synchronizer\Checksum;
use Grixu\Synchronizer\Contracts\ParserInterface;
use Grixu\Synchronizer\Contracts\SingleElementParserInterface;
use Illuminate\Support\Collection;

abstract class AbstractParser implements ParserInterface, SingleElementParserInterface
{
    public function parse(Collection $collection): Collection
    {
        $timestampExcluded = config('synchronizer.checksum.timestamps_excluded');

        return $collection->map(function ($item) use ($timestampExcluded) {
            $item = $this->parseElement($item);

            if (!$timestampExcluded) {
                $item = $item->except('synchronizer.sync.timestamps');
            }

            $item->checksum = Checksum::generate($item->toArray());
            return $item;
        });
    }
}
