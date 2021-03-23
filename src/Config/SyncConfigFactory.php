<?php

namespace Grixu\Synchronizer\Config;

use Grixu\Synchronizer\Contracts\ErrorHandlerInterface;
use Grixu\Synchronizer\Contracts\SyncHandlerInterface;
use Grixu\Synchronizer\Traits\CheckClassImplementsInterface;
use Illuminate\Queue\SerializableClosure;

class SyncConfigFactory
{
    use CheckClassImplementsInterface;

    public function make(
        string $loaderClass,
        string $parserClass,
        string $localModel,
        string $foreignKey,
        ?array $idsToSync = [],
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

        return new SyncConfig(
            loaderClass: $loaderClass,
            parserClass: $parserClass,
            localModel: $localModel,
            foreignKey: $foreignKey,
            idsToSync: $idsToSync,
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
