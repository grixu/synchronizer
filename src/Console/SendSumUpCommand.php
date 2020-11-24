<?php

namespace Grixu\Synchronizer\Console;

use Grixu\Synchronizer\Models\SynchronizerLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Class SendSumUpCommand
 * @package Grixu\Synchronizer\Console
 */
class SendSumUpCommand extends Command
{
    protected $signature = 'synchronizer:send';

    protected $description = 'Send daily sum up about sync';

    public function handle()
    {
        $lastUpdate = Cache::get('synchronizer-update', null);

        $collection = SynchronizerLog::query();

        if (!empty($lastUpdate)) {
            $collection->whereDate('created_at', '>=', $lastUpdate);
        }

        $collection = $collection->get()
            ->groupBy('model');


        if(config('synchronizer.send_slack_sum_up') == true && !empty(config('logging.slack.url'))) {
            // Send info to Slack groupedBy models
            foreach ($collection as $key => $data) {
                Log::channel('slack')->notice('Zmienionych rekordów dla modelu '. $key .': '.$data->count());
            }
        }


        $this->info('Done!');

        Cache::put('synchronizer-update', now());

        return 0;
    }
}
