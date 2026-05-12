<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GeneralNotification extends Notification
{
    use Queueable;

    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => $this->data['message'],
            'type' => $this->data['type'],
            'url' => $this->data['url'] ?? null,
            'timestamp' => now(),
        ];
    }

    public function toArray($notifiable)
    {
        return $this->toDatabase($notifiable);
    }
}
