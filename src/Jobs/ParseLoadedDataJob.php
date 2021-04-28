<?php

namespace Grixu\Synchronizer\Jobs;

use Grixu\Synchronizer\Config\SyncConfig;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Grixu\Synchronizer\Contracts\ParserInterface;

class ParseLoadedDataJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $timeout = 300;
    public $tries = 0;
    public $maxExceptions = 3;

    public function __construct(public Collection $dataToParse, public SyncConfig $config)
    {
    }

    public function backoff(): int
    {
        return 30 * $this->attempts();
    }

    public function retryUntil(): Carbon
    {
        return now()->addSeconds(
            $this->timeout * $this->maxExceptions + $this->backoff()
        );
    }

    public function handle()
    {
        if (optional($this->batch())->cancelled()) {
            return;
        }

        $parserClass = $this->config->getParserClass();
        /** @var ParserInterface $parser */
        $parser = app($parserClass);

        $dtoCollection = $parser->parse($this->dataToParse);

        if ($this->batch()) {
            $jobClass = $this->config->getNextJob();

            $this->batch()->add(
                [
                    new $jobClass($dtoCollection, $this->config)
                ]
            );
        }
    }
}
