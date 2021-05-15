<?php

namespace Grixu\Synchronizer;

use Grixu\Synchronizer\Models\Log;
use Grixu\Synchronizer\Notifications\LoggerNotification;
use Illuminate\Support\Facades\Notification;

class Logger
{
    public const BELONGS_TO = 1;
    public const MODEL = 2;
    public const BELONGS_TO_MANY = 3;

    public function __construct(protected string $batchId, protected string $model)
    {
    }

    public function log(array $changes, int $type): void
    {
        if (count($changes) > 0) {
            Log::create(
                [
                    'model' => $this->model,
                    'batch_id' => $this->batchId,
                    'changed' => count($changes),
                    'type' => $type,
                    'log' => $changes,
                ]
            );
        }
    }

    public function report(): void
    {
        if (empty(config('synchronizer.logger.notifications.slack'))) {
            return;
        }

        $count = Log::query()
            ->where(
                [
                    ['batch_id', '=', $this->batchId],
                    ['model', '=', $this->model]
                ]
            )
            ->sum('changed');

        if ($count > 0) {
            Notification::route('slack', config('synchronizer.logger.notifications.slack'))
                ->notify(new LoggerNotification($this->model, $count));
        }
    }
}
