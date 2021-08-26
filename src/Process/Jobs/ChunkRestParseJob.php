<?php

namespace Grixu\Synchronizer\Process\Jobs;

use Grixu\Synchronizer\Config\Contracts\EngineConfigInterface;
use Grixu\Synchronizer\Config\Contracts\ProcessConfigInterface;
use Grixu\Synchronizer\Config\EngineConfig;
use Grixu\Synchronizer\Process\Contracts\LoaderInterface;
use Grixu\Synchronizer\Process\Contracts\ParserInterface;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Carbon;
use Throwable;

class ChunkRestParseJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public $timeout = 180;
    public $tries = 0;
    public $maxExceptions = 3;

    public function __construct(
        public ProcessConfigInterface $processConfig,
        public EngineConfigInterface $engineConfig,
        public int $part = 1
    ) {
    }

    public function retryUntil(): Carbon
    {
        return now()
            ->addSeconds(
                $this->timeout * $this->maxExceptions + $this->backoff()
            );
    }

    public function backoff(): int
    {
        return 60 * $this->attempts();
    }

    public function handle()
    {
        if (optional($this->batch())->cancelled() || !$this->batch()) {
            return;
        }

        EngineConfig::setInstance($this->engineConfig);

        $loaderClass = $this->processConfig->getLoaderClass();
        /** @var LoaderInterface $loader */
        $loader = app($loaderClass);
        $loader->buildQuery($this->engineConfig->getIds());

        $parserClass = $this->processConfig->getParserClass();
        /** @var ParserInterface $parser */
        $parser = app($parserClass);

        $jobClass = $this->processConfig->getNextJob();
        try {
            $data = $loader->getPiece($this->part);
        } catch (Throwable) {
            $this->release(config('synchronizer.queues.release'));
        }

        if ($data->count() <= 0) {
            return;
        }

        $parsedData = $parser->parse($data)->toArray();
        $this->batch()->add((new $jobClass($this->processConfig, $this->engineConfig, $parsedData)));

        $this->part++;
        $this->batch()->add([new static($this->processConfig, $this->engineConfig, $this->part)]);
    }
}
