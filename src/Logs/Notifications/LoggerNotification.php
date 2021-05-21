<?php

namespace Grixu\Synchronizer\Logs\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class LoggerNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $model, public int $totalChanges)
    {
    }

    public function via(): array
    {
        return ['slack'];
    }

    public function toSlack($notifiable): SlackMessage
    {
        return (new SlackMessage)
            ->content(
                'Synchronizacja modelu ' . $this->model . ' zakończona. Wprowadzonych zmian: ' . $this->totalChanges
            );
    }
}
