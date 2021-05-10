<?php

namespace Grixu\Synchronizer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Checksum
{
    public static string $checksumField = 'checksum';
    protected Collection $diff;

    public function __construct(Collection $data, string $key, string $model)
    {
        $modelKey = Str::snake($key);

        $checksums = $data->pluck(static::$checksumField, $key);
        /** @var Model $model */
        $storedChecksums = $model::query()
            ->whereIn($modelKey, $checksums->keys())
            ->whereNotNull(static::$checksumField)
            ->pluck(static::$checksumField, $modelKey);

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

    public function get(): Collection
    {
        return $this->diff;
    }

    public static function generate(array $data): string
    {
        return hash('crc32c', json_encode($data));
    }

    public static function setChecksumField(string $fieldName)
    {
        static::$checksumField = $fieldName;
    }
}
