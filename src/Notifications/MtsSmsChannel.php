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
        if (!method_exists($notification, 'toMtsSms')) {
            throw new \RuntimeException(
                'Notification is missing toMtsSms method'
            );
        }

        $message = $notification->toMtsSms($notifiable);

        if (!$message instanceof MtsSmsMessage) {
            throw new \RuntimeException(
                'toMtsSms must return instance of MtsSmsMessage'
            );
        }

        return $this->smsService->send(
            $this->getRecipient($notifiable),
            $message->content,
            $message->params
        );
    }

    protected function getRecipient($notifiable): string
    {
        if ($recipient = $notifiable->routeNotificationFor('mts-sms')) {
            return $recipient;
        }

        if (isset($notifiable->phone)) {
            return $notifiable->phone;
        }

        throw new \RuntimeException('No recipient specified for MTS SMS');
    }
}
