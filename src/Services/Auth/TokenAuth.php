<?php

namespace RuTrue\SmsMts\Services\Auth;

use Illuminate\Support\Facades\Http;
use RuTrue\SmsMts\Exceptions\SmsMtsException;

class TokenAuth
{
	protected string $token;
	protected string $apiUrl;

	public function __construct()
	{
		$this->token = config('sms-mts.token');
		$this->apiUrl = config('sms-mts.api_urls.token');
	}

	public function send(array $payload)
	{
		return Http::withToken($this->token)
			->timeout(config('sms-mts.timeout'))
			->post($this->apiUrl, $payload);
	}
}
