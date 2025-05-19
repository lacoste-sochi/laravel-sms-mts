<?php

namespace RuTrue\SmsMts\Notifications;

use Illuminate\Notifications\Notification;
use RuTrue\SmsMts\Contracts\SmsMtsDriverInterface;

class SmsMtsChannel
{
	public function __construct(
		protected SmsMtsDriverInterface $smsService
	) {
	}

	public function send($notifiable, Notification $notification)
	{
		$message = $notification->toSmsMts($notifiable);

		// Если возвращается строка - преобразуем в объект сообщения
		if (is_string($message)) {
			$message = new SmsMtsMessage($message);
		}

		// Получаем телефон получателя
		$phone = $notifiable->routeNotificationFor('sms-mts', $notification);

		// Отправляем SMS и возвращаем результат для HTTP-ответа
		return $this->smsService->send(
			$phone,
			$message->content,
			$message->params ?? []
		);
	}
}
