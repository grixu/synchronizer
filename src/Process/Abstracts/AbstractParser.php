<?php

namespace Grixu\Synchronizer\Process\Abstracts;

use Grixu\Synchronizer\Engine\Contracts\EngineConfigInterface;
use Grixu\Synchronizer\Process\Contracts\ParserInterface;
use Grixu\Synchronizer\Process\Contracts\SingleElementParserInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

abstract class AbstractParser implements ParserInterface, SingleElementParserInterface
{
    public function parse(Collection $collection): Collection
    {
        /** @var EngineConfigInterface $config */
        $config = app(EngineConfigInterface::class);

        return $collection->map(function ($item) use ($config) {
            $data = $this->parseElement($item)->toArray();

            $fillableFields = Arr::only($data, $config->getFillable());

            if ($config->isOnlyMode()) {
                $item = Arr::only($data, $config->getOnly());
            } else {
                $item = Arr::except($data, array_merge($config->getExcluded(), $config->getFillable()));
            }

            $checksumBase = $item;
            if (!empty($config->getTimestamps())) {
                $checksumBase = Arr::except($checksumBase, $config->getTimestamps());
            }

            if (!empty($config->getChecksumField())) {
                $item['checksum'] = $this->generateChecksum($checksumBase);
            }
            if (!$config->isOnlyMode()) {
                $item['fillable'] = $fillableFields;
            }

            return $item;
        });
    }

    protected function generateChecksum(array $data): string
    {
        return hash('crc32c', json_encode($data));
    }
}
