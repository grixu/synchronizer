<?php

namespace Grixu\Synchronizer;

use Grixu\Synchronizer\Engine\Contracts\EngineConfigInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Checksum
{
    protected Collection $diff;

    public function __construct(Collection $data, EngineConfigInterface $config)
    {
        $this->isChecksumControlDisabled();
        $key = $config->getKey();
        $model = $config->getModel();

        $checksumField = Str::snake($config->getChecksumField());
        $modelKey = Str::snake($key);

        $checksums = $data->pluck($checksumField, $key);
        /** @var Model $model */
        $storedChecksums = $model::query()
            ->whereIn($modelKey, $checksums->keys())
            ->whereNotNull($checksumField)
            ->pluck($checksumField, $modelKey);

        $diff = collect();

        foreach ($checksums as $id => $checksum) {
            if (isset($storedChecksums[$id]) && $storedChecksums[$id] !== $checksum) {
                $diff->push(
                    $data->where($key, $id)->first()
                );
            }
        }

        $differentialKeyCheck = $checksums->keys()->diff($storedChecksums->keys());
        $diff->push(...$data->whereIn($key, $differentialKeyCheck));

        $this->diff = $diff->filter();
    }

    protected function isChecksumControlDisabled(): void
    {
        if (!config('synchronizer.checksum.control')) {
            throw new \Exception('Checksum checking is turned off');
        }
    }

    public function get(): Collection
    {
        return $this->diff;
    }

    public static function generate(array $data): string
    {
        return hash('crc32c', json_encode($data));
    }
}
