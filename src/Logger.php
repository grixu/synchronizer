<?php

namespace Grixu\Synchronizer;

use Grixu\Synchronizer\Models\Log;
use Grixu\Synchronizer\Notifications\LoggerNotification;
use Illuminate\Support\Facades\Notification;

class Logger
{
    public function __construct(protected string $batchId, protected string $model)
    {
    }

    public function log(array $changes): void
    {
        if (count($changes) > 0) {
            Log::create(
                [
                    'model' => $this->model,
                    'batch_id' => $this->batchId,
                    'total_changes' => count($changes),
                    'log' => $changes,
                ]
            );
        }
    }

    public function report(): void
    {
        $count = Log::query()
            ->where(
                [
                    ['batch_id', '=', $this->batchId],
                    ['model', '=', $this->model]
                ]
            )
            ->sum('total_changes');

        ray($count);
        if ($count > 0) {
            Notification::route('slack', config('synchronizer.logger.notifications.slack'))
                ->notify(new LoggerNotification($this->model, $count));
        }
    }
}
