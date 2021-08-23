<?php

namespace Grixu\Synchronizer\Process\Abstracts;

use Grixu\Synchronizer\Checksum;
use Grixu\Synchronizer\Config\Contracts\SyncConfig;
use Grixu\Synchronizer\Process\Contracts\ParserInterface;
use Grixu\Synchronizer\Process\Contracts\SingleElementParserInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

abstract class AbstractParser implements ParserInterface, SingleElementParserInterface
{
    public function parse(Collection $collection): Collection
    {
        $config = app(SyncConfig::class);
        $timestampExcluded = config('synchronizer.checksum.timestamps_excluded');
        $timestamps = $config->getTimestamps();
        $timestamps = array_map(fn ($item) => Str::camel($item), $timestamps);

        return $collection->map(function ($item) use ($timestampExcluded, $timestamps) {
            $item = $this->parseElement($item);
            $checksumBase = clone $item;

            if ($timestampExcluded) {
                $checksumBase = $checksumBase->except(...$timestamps);
            }

            $item = $item->toArray();
            $item['checksum'] = Checksum::generate($checksumBase->toArray());
            return $item;
        });
    }
}
