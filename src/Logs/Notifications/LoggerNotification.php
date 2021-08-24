<?php

namespace Grixu\Synchronizer\Logs\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class LoggerNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $tries = 2;
    public $timeout = 30;
    public $backoff = [5, 20];

    public function __construct(public string $model, public int $totalChanges)
    {
        $this->onQueue(config('synchronizer.queues.notifications'));
    }

    public function via(): array
    {
        return ['slack'];
    }

    public function toSlack($notifiable): SlackMessage
    {
        return (new SlackMessage())
            ->content(
                'Synchronizacja modelu ' . $this->model . ' zakończona. Wprowadzonych zmian: ' . $this->totalChanges
            );
    }
}
