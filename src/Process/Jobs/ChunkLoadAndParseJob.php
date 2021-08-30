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

class ChunkLoadAndParseJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public $timeout = 600;
    public $tries = 0;
    public $maxExceptions = 3;

    public function __construct(public ProcessConfigInterface $processConfig, public EngineConfigInterface $engineConfig)
    {
    }

    public function backoff(): int
    {
        return 60 * $this->attempts();
    }

    public function retryUntil(): Carbon
    {
        return now()
            ->addSeconds(
                $this->timeout * $this->maxExceptions + $this->backoff()
            );
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

        $loader->chunk(
            function ($data) use ($jobClass, $parser) {
                $parsedData = $parser->parse($data)->toArray();
                $this->batch()->add((new $jobClass($this->processConfig, $this->engineConfig, $parsedData)));
            }
        );
    }
}
