<?php

namespace Grixu\Synchronizer\Config;

use Exception;
use Grixu\Synchronizer\Process\Contracts\ErrorHandlerInterface;
use Grixu\Synchronizer\Process\Contracts\SyncHandlerInterface;
use Grixu\Synchronizer\Config\Traits\CheckClassImplementsInterface;
use Illuminate\Queue\SerializableClosure;

class SyncConfigFactory
{
    use CheckClassImplementsInterface;

    public function make(
        string $loaderClass,
        string $parserClass,
        string $localModel,
        string $foreignKey,
        array|string|null $jobsConfig = null,
        string|bool|null $checksumField = null,
        array|bool $timestamps = [],
        array $ids = [],
        SerializableClosure|null $syncClosure = null,
        SerializableClosure|null $errorHandler = null
    ): SyncConfig {
        if (empty($syncClosure)) {
            $syncClosure = $this->useConfigDefaults(
                'synchronizer.handlers.sync',
                SyncHandlerInterface::class
            );
        }

        if (empty($errorHandler)) {
            $errorHandler = $this->useConfigDefaults(
                'synchronizer.handlers.error',
                ErrorHandlerInterface::class
            );
        }

        if (config('synchronizer.checksum.control')) {
            if (empty($checksumField) && $checksumField !== false) {
                $checksumField = config('synchronizer.checksum.field');
            }

            if ($checksumField === false) {
                $checksumField = null;
            }

            if (empty($timestamps) && $timestamps !== false) {
                $timestamps = config('synchronizer.checksum.timestamps');
            }

            if ($timestamps === false) {
                $timestamps = [];
            }
        }

        if (!empty($jobsConfig) && is_string($jobsConfig)) {
            $jobsConfig = config('synchronizer.jobs.' . $jobsConfig);
        }

        if (empty($jobsConfig)) {
            $jobsConfig = config('synchronizer.jobs.default');
        }

        if (empty($jobsConfig)) {
            throw new Exception('Empty jobs configuration');
        }

        return new SyncConfig(
            loaderClass: $loaderClass,
            parserClass: $parserClass,
            localModel: $localModel,
            foreignKey: $foreignKey,
            jobsConfig: $jobsConfig,
            checksumField: $checksumField,
            timestamps: $timestamps,
            ids: $ids,
            syncClosure: $syncClosure,
            errorHandler: $errorHandler
        );
    }

    protected function useConfigDefaults(string $config, string $interface): SerializableClosure|null
    {
        $config = config($config);
        if (!empty($config)) {
            self::checkClassIsImplementingInterface($config, $interface);

            /** @var SyncHandlerInterface|ErrorHandlerInterface $config */
            return $config::make();
        }

        return null;
    }
}
