<?php

namespace Grixu\Synchronizer\Process\Abstracts;

use Grixu\Synchronizer\Engine\Contracts\EngineConfigInterface;
use Grixu\Synchronizer\Process\Contracts\ParserInterface;
use Grixu\Synchronizer\Process\Contracts\SingleElementParserInterface;
use Illuminate\Support\Collection;
use Spatie\DataTransferObject\DataTransferObject;

abstract class AbstractParser implements ParserInterface, SingleElementParserInterface
{
    public function parse(Collection $collection): Collection
    {
        /** @var EngineConfigInterface $config */
        $config = app(EngineConfigInterface::class);

        return $collection->map(function ($item) use ($config) {
            $item = $this->parseElement($item);
            $fillableFields = $item->only(...$config->getFillable())->toArray();

            if ($config->isOnlyMode()) {
                $item = $item->only(...$config->getOnly());
            } else {
                $item = $item->except(...$config->getExcluded(), ...$config->getFillable());
            }

            $checksumBase = clone $item;

            if (!empty($config->getTimestamps())) {
                $checksumBase = $checksumBase->except(...$config->getTimestamps());
            }

            $item = $item->toArray();
            if (!empty($config->getChecksumField())) {
                $item['checksum'] = $this->generateChecksum($checksumBase);
            }
            if (!$config->isOnlyMode()) {
                $item['fillable'] = $fillableFields;
            }
            return $item;
        });
    }

    protected function generateChecksum(DataTransferObject $dataTransferObject): string
    {
        return hash('crc32c', json_encode($dataTransferObject->toArray()));
    }
}
