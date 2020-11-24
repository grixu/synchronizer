<?php

namespace Grixu\Synchronizer\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Class SetBeginningForSumUpsCommand
 * @package Grixu\Synchronizer\Tests\Commands
 */
class SetBeginningForSumUpsCommand extends Command
{
    protected $signature = 'synchronizer:set';

    protected $description = 'Set timestamp for sum up sender';

    public function handle()
    {
        Cache::put('synchronizer-update', now());

        $this->info('Done!');

        return 0;
    }
}
