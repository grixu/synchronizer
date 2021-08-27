<?php

namespace Grixu\Synchronizer\Process\Abstracts;

use Grixu\Synchronizer\Checksum;
use Grixu\Synchronizer\Config\Contracts\EngineConfigInterface;
use Grixu\Synchronizer\Process\Contracts\ParserInterface;
use Grixu\Synchronizer\Process\Contracts\SingleElementParserInterface;
use Illuminate\Support\Collection;

abstract class AbstractParser implements ParserInterface, SingleElementParserInterface
{
    public function parse(Collection $collection): Collection
    {
        /** @var EngineConfigInterface $config */
        $config = app(EngineConfigInterface::class);
        $timestampExcluded = config('synchronizer.checksum.timestamps_excluded');

        return $collection->map(function ($item) use ($timestampExcluded, $config) {
            $item = $this->parseElement($item);
            $item = $item->except(...$config->getExcludedFields());
            $checksumBase = clone $item;

            if ($timestampExcluded) {
                $checksumBase = $checksumBase->except(...$config->getTimestamps());
            }

            $item = $item->toArray();
            $item['checksum'] = Checksum::generate($checksumBase->toArray());
            return $item;
        });
    }
}
