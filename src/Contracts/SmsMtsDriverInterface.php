<?php

namespace RuTrue\SmsMts\Contracts;

interface SmsMtsDriverInterface
{
	public function send(string $phone, string $message, array $params = []);
	public function bulkSend(array $messages);
}
