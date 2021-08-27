<?php

namespace Grixu\Synchronizer\Process\Actions;

use Grixu\Synchronizer\Config\Contracts\EngineConfigInterface;
use Grixu\Synchronizer\Config\Contracts\ProcessConfigInterface;
use Grixu\Synchronizer\Config\EngineConfigFactory;
use Grixu\Synchronizer\Config\ProcessConfig;
use Grixu\Synchronizer\Process\Contracts\ErrorHandlerInterface;
use Grixu\Synchronizer\Process\Events\CollectionSynchronizedEvent;
use Illuminate\Bus\Batch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Throwable;

class StartSyncAction
{
    private Collection $configs;
    private array $jobs;
    private string $queue = 'default';

    public function __construct(array $configs)
    {
        $this->configs = collect();
        $this->jobs = [];

        $this->processConfigs($configs);
        $this->processJobs();
    }

    protected function processConfigs(array $configs)
    {
        foreach ($configs as ['process' => $process, 'engine' => $engine]) {
            if (is_array($process)) {
                $process = ProcessConfig::make(...$process);
            }
            if (!$process instanceof ProcessConfigInterface) {
                continue;
            }

            if (is_array($engine)) {
                $engine = EngineConfigFactory::make(...$engine);
            }
            if (!$engine instanceof EngineConfigInterface) {
                continue;
            }

            $this->configs->push(['process' => $process, 'engine' => $engine]);
        }
    }

    protected function processJobs()
    {
        foreach ($this->configs as ['process' => $process, 'engine' => $engine]) {
            /** @var ProcessConfigInterface $config */
            $jobClass = $process->getCurrentJob();

            $this->jobs[] = new $jobClass($process, $engine);
        }
    }

    public function onQueue(string $queue): static
    {
        $this->queue = $queue;

        return $this;
    }

    public function dispatch(): Batch
    {
        return Bus::batch($this->jobs)
            ->allowFailures()
            // @codeCoverageIgnoreStart
            ->then(function (Batch $batch) {
                foreach ($this->configs as ['engine' => $config]) {
                    /** @var EngineConfigInterface $config */
                    event(new CollectionSynchronizedEvent($config->getModel(), $config->getChecksumField(), $batch->id));
                }
            })
            ->catch(function (Batch $batch, Throwable $exception) {
                foreach ($this->configs as ['process' => $config]) {
                    /** @var ProcessConfigInterface $config */
                    /** @var ErrorHandlerInterface $handler */
                    $handler = app($config->getErrorHandler());

                    $handler->handle($exception);
                };
            })
            // @codeCoverageIgnoreEnd
            ->onQueue($this->queue)
            ->dispatch();
    }
}
