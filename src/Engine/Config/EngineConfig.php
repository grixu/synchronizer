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
        $this->key = Str::camel($key);
        $this->checksumField = Str::camel($checksumField);
        $this->timestamps = array_map(fn ($item) => Str::camel($item), $timestamps);

        $this->mapFields($mode, $fields);
        $this->secureKeyInFields();
        $this->validateChecksum();
    }

    protected function mapFields(int $mode, array $fields): void
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

    private function mapFieldsExcluded(array $excludedFields): void
    {
        foreach ($excludedFields as $key => $value) {
            if (is_array($value)) {
                if ($key === 'fillable') {
                    $this->fillableFields = array_map(fn ($item) => Str::camel($item), $value);
                    continue;
                }

                if ($key === 'excluded' || $key === 0) {
                    $this->excludedFields = array_map(fn ($item) => Str::camel($item), $value);
                    continue;
                }
            }

            $this->excludedFields[] = Str::camel($value);
        }
    }

    private function secureKeyInFields(): void
    {
        if ($this->isOnlyMode()) {
            if (!in_array($this->key, $this->onlyFields)) {
                $this->onlyFields[] = $this->key;
            }
        } else {
            $this->excludedFields = array_diff($this->excludedFields, [$this->key]);
            $this->fillableFields = array_diff($this->fillableFields, [$this->key]);
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
        if (empty(self::$instance)) {
            self::$instance = new NullEngineConfig();
        }
        // @codeCoverageIgnoreEnd

        return self::$instance;
    }

    public static function setInstance(EngineConfigInterface $instance)
    {
        self::$instance = $instance;
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
