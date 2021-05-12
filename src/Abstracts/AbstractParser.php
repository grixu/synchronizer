<?php

namespace Grixu\Synchronizer\Abstracts;

use Grixu\Synchronizer\Checksum;
use Grixu\Synchronizer\Contracts\ParserInterface;
use Grixu\Synchronizer\Contracts\SingleElementParserInterface;
use Grixu\Synchronizer\Map;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

abstract class AbstractParser implements ParserInterface, SingleElementParserInterface
{
    public function parse(Collection $collection): Collection
    {
        $timestampExcluded = config('synchronizer.checksum.timestamps_excluded');
        $timestamps = Map::getTimestamps();
        $timestamps = array_map(fn ($item) => Str::camel($item), $timestamps);

        return $collection->map(function ($item) use ($timestampExcluded, $timestamps) {
            $item = $this->parseElement($item);

            if (!$timestampExcluded) {
                $item = $item->except(...$timestamps);
            }

            $item->checksum = Checksum::generate($item->toArray());
            return $item;
        });
    }
}
