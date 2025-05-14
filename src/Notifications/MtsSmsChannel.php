<?php

namespace Rutrue\MtsSms\Notifications;

use Illuminate\Notifications\Notification;
use Rutrue\MtsSms\Services\MtsSmsService;

class MtsSmsChannel
{
    public function __construct(private MtsSmsService $smsService)
    {
    }

    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toMtsSms($notifiable);

        return $this->smsService->send(
            $notifiable->routeNotificationFor('mts-sms'),
            $message->content,
            $message->params ?? []
        );
    }
}