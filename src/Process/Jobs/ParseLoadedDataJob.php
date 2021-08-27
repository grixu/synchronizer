<?php

namespace Grixu\Synchronizer\Process\Jobs;

use Grixu\Synchronizer\Config\Contracts\ProcessConfigInterface;
use Grixu\Synchronizer\Engine\Config\EngineConfig;
use Grixu\Synchronizer\Engine\Contracts\EngineConfigInterface;
use Grixu\Synchronizer\Process\Contracts\ParserInterface;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ParseLoadedDataJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public $timeout = 300;
    public $tries = 0;
    public $maxExceptions = 3;

    public function __construct(
        public ProcessConfigInterface $processConfig,
        public EngineConfigInterface $engineConfig,
        public Collection $dataToParse
    ) {
    }

    public function backoff(): int
    {
        return 60 * $this->attempts();
    }

    public function retryUntil(): Carbon
    {
        return now()->addHour();
    }

    public function handle()
    {
        if (optional($this->batch())->cancelled()) {
            return;
        }

        EngineConfig::setInstance($this->engineConfig);

        $parserClass = $this->processConfig->getParserClass();
        /** @var ParserInterface $parser */
        $parser = app($parserClass);

        $data = $parser->parse($this->dataToParse)->toArray();

        if ($this->batch()) {
            $jobClass = $this->processConfig->getNextJob();

            $this->batch()->add(
                [
                    new $jobClass($this->processConfig, $this->engineConfig, $data),
                ]
            );
        }
    }
}
