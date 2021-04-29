<?php

namespace Grixu\Synchronizer\Jobs;

use Grixu\Synchronizer\Config\SyncConfig;
use Grixu\Synchronizer\Contracts\LoaderInterface;
use Grixu\Synchronizer\Contracts\ParserInterface;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
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

    public function __construct(public SyncConfig $config)
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

        $loaderClass = $this->config->getLoaderClass();
        /** @var LoaderInterface $loader */
        $loader = app($loaderClass);
        $loader->buildQuery($this->config->getIdsToSync());
        /** @var Builder $query */
        $query = $loader->getBuilder();

        $parserClass = $this->config->getParserClass();
        /** @var ParserInterface $parser */
        $parser = app($parserClass);

        $jobClass = $this->config->getNextJob();

        $query->chunk(
            config('synchronizer.sync.default_chunk_size'),
            function ($data) use ($jobClass, $parser) {
                $parsedData = $parser->parse($data);
                $this->batch()->add((new $jobClass($parsedData, $this->config)));
            }
        );
    }
}
