<?php

namespace Grixu\Synchronizer\Jobs;

use Grixu\Synchronizer\Config\SyncConfig;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Grixu\Synchronizer\Contracts\ParserInterface;

class ParseLoadedDataJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public Collection $dataToParse, public SyncConfig $config)
    {
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
            $jobClass = config('synchronizer.jobs.sync');

            $this->batch()->add(
                [
                    new $jobClass($dtoCollection, $this->config)
                ]
            );
        }
    }
}
