<?php

namespace Grixu\Synchronizer\Process\Jobs;

use Grixu\Synchronizer\Engine\Config\EngineConfig;
use Grixu\Synchronizer\Engine\Contracts\EngineConfigInterface;
use Grixu\Synchronizer\Process\Contracts\LoaderInterface;
use Grixu\Synchronizer\Process\Contracts\ParserInterface;
use Grixu\Synchronizer\Process\Contracts\ProcessConfigInterface;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Carbon;

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
        return config('synchronizer.queues.release') * $this->attempts();
    }

    public function handle()
    {
//        if (optional($this->batch())->cancelled() || !$this->batch()) {
//            return;
//        }

        EngineConfig::setInstance($this->engineConfig);

        $loaderClass = $this->processConfig->getLoaderClass();
        /** @var LoaderInterface $loader */
        $loader = app($loaderClass);
        $loader->buildQuery($this->engineConfig->getIds());

        $parserClass = $this->processConfig->getParserClass();
        /** @var ParserInterface $parser */
        $parser = app($parserClass);

        $jobClass = $this->processConfig->getNextJob();
        $data = $loader->getPiece($this->part);

        if ($data->count() <= 0) {
            return;
        }

        $parsedData = $parser->parse($data)->toArray();
        $this->batch()->add((new $jobClass($this->processConfig, $this->engineConfig, $parsedData)));

        $this->part++;
        $this->batch()->add([new static($this->processConfig, $this->engineConfig, $this->part)]);
    }
}
