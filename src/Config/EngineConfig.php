<?php

namespace Grixu\Synchronizer\Config;

use Grixu\Synchronizer\Config\Contracts\EngineConfigInterface;

class EngineConfig implements EngineConfigInterface
{
    private static EngineConfigInterface|null $instance = null;

    public function __construct(
        protected string $model,
        protected string $key,
        protected array $excludedFields = [],
        protected string|null $checksumField = null,
        protected array $timestamps = [],
        protected array $ids = [],
    ) {
        $this->validateChecksum();
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
        if (empty(static::$instance)) {
            static::$instance = new NullEngineConfig();
        }

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

    public function getExcludedFields(): array
    {
        return $this->excludedFields;
    }
}
