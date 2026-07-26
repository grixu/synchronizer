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

        return $collection->map(
            fn ($item) => $this->buildSyncRow($this->parseElement($item)->toArray(), $config)
        );
    }

    protected function buildSyncRow(array $parsed, EngineConfigInterface $config): array
    {
        if ($config->isOnlyMode()) {
            return $this->withChecksum(Arr::only($parsed, $config->getOnly()), $config);
        }

        $syncRow = Arr::except($parsed, array_merge($config->getExcluded(), $config->getFillable()));
        $syncRow = $this->withChecksum($syncRow, $config);
        $syncRow['fillable'] = Arr::only($parsed, $config->getFillable());

        return $syncRow;
    }

    protected function withChecksum(array $syncRow, EngineConfigInterface $config): array
    {
        if (empty($config->getChecksumField())) {
            return $syncRow;
        }

        $syncRow['checksum'] = $this->generateChecksum(
            Arr::except($syncRow, $config->getTimestamps())
        );

        return $syncRow;
    }

    protected function generateChecksum(array $checksumBase): string
    {
        return hash('crc32c', json_encode($checksumBase));
    }
}
