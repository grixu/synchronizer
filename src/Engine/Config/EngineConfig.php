<?php

namespace Grixu\Synchronizer\Engine\Config;

use Grixu\Synchronizer\Engine\Contracts\EngineConfigInterface;
use Illuminate\Support\Str;

class EngineConfig implements EngineConfigInterface
{
    private static EngineConfigInterface|null $instance = null;

    public const ONLY = 1;
    public const EXCLUDED = 2;

    protected array $excludedFields = [];
    protected array $fillableFields = [];
    protected array $onlyFields = [];

    public function __construct(
        protected string $model,
        protected string $key,
        array $fields = [],
        int $mode = self::EXCLUDED,
        protected string|null $checksumField = null,
        protected array $timestamps = [],
        protected array $ids = [],
    ) {
        $this->checksumField = Str::camel($checksumField);
        $this->timestamps = array_map(fn ($item) => Str::camel($item), $timestamps);

        $this->mapFields($mode, $fields);
        $this->validateChecksum();
    }

    protected function mapFields(int $mode, array $fields)
    {
        switch ($mode) {
            case self::ONLY:
                $this->onlyFields = array_map(fn ($item) => Str::camel($item), $fields);
                $this->excludedFields = [];
                $this->fillableFields = [];
                break;
            case self::EXCLUDED:
                $this->onlyFields = [];
                $this->mapFieldsExcluded($fields);
                break;
        }
    }

    private function mapFieldsExcluded(array $excludedFields)
    {
        foreach ($excludedFields as $key => $value) {
            if (is_array($value)) {
                if (in_array('fillable', $value)) {
                    $this->fillableFields[] = Str::camel($key);
                    continue;
                }

                $this->excludedFields[] = Str::camel($key);
            } else {
                $this->excludedFields[] = Str::camel($value);
            }
        }
    }

    protected function validateChecksum(): void
    {
        if (!config('synchronizer.checksum.control')) {
            $this->checksumField = null;
            $this->timestamps = [];
        }
    }

    public static function getInstance(): EngineConfigInterface
    {
        // @codeCoverageIgnoreStart
        if (empty(static::$instance)) {
            static::$instance = new NullEngineConfig();
        }
        // @codeCoverageIgnoreEnd

        return static::$instance;
    }

    public static function setInstance(EngineConfigInterface $instance)
    {
        static::$instance = $instance;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getTimestamps(): array
    {
        return $this->timestamps;
    }

    public function getIds(): array
    {
        return $this->ids;
    }

    public function getChecksumField(): string | null
    {
        return $this->checksumField;
    }

    public function getExcluded(): array
    {
        return $this->excludedFields;
    }

    public function getFillable(): array
    {
        return $this->fillableFields;
    }

    public function getOnly(): array
    {
        return $this->onlyFields;
    }

    public function isOnlyMode(): bool
    {
        return count($this->onlyFields) > 0;
    }
}
