<?php

namespace RuTrue\SmsMts\Exceptions;

use Exception;

class SmsMtsException extends Exception
{
	public function __construct(
		string $message = "",
		int $code = 0,
		public ?string $errorCode = null
	) {
		parent::__construct($message, $code);
	}
}
