<?php

namespace Grixu\Synchronizer\Process\Jobs;

use Grixu\Synchronizer\Config\SyncConfig;
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

    public function __construct(public SyncConfig $config, public int $part = 1)
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

        SyncConfig::setInstance($this->config);

        $loaderClass = $this->config->getLoaderClass();
        /** @var LoaderInterface $loader */
        $loader = app($loaderClass);
        $loader->buildQuery($this->config->getIds());

        $parserClass = $this->config->getParserClass();
        /** @var ParserInterface $parser */
        $parser = app($parserClass);

        $jobClass = $this->config->getNextJob();
        try {
            $data = $loader->getPiece($this->part);
        } catch (Throwable) {
            $this->release(config('synchronizer.queues.release'));
        }

        if ($data->count() <= 0) {
            return;
        }

        $parsedData = $parser->parse($data)->toArray();
        $this->batch()->add((new $jobClass($parsedData, $this->config)));

        $this->part++;
        $this->batch()->add([new static($this->config, $this->part)]);
    }
}
