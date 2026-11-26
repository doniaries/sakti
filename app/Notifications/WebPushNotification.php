<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class WebPushNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $body,
        public string $url = '/mobile/dashboard',
    ) {}

    /**
     * @return array<int, class-string>
     */
    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush(object $notifiable, object $notification): WebPushMessage
    {
        return (new WebPushMessage())
            ->title($this->title)
            ->body($this->body)
            ->icon('/icons/icon-192x192.png?v=2')
            ->badge('/icons/icon-192x192.png?v=2')
            ->data(['url' => $this->url])
            ->options(['TTL' => 3600]);
    }
}
