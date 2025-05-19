<?php

namespace RuTrue\SmsMts\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static send(string $phone, string $message, array $params = [])
 * @method static bulkSend(array $messages)
 */
class SmsMts extends Facade
{
	protected static function getFacadeAccessor()
	{
		return \RuTrue\SmsMts\Contracts\SmsMtsDriverInterface::class;
	}
}
