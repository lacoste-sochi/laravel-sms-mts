<?php

namespace RuTrue\SmsMts\Services\Auth;

use Illuminate\Support\Facades\Http;
use RuTrue\SmsMts\Exceptions\SmsMtsException;

class BasicAuth
{
	protected string $login;
	protected string $password;
	protected string $apiUrl;

	public function __construct()
	{
		$this->login = config('sms-mts.login');
		$this->password = config('sms-mts.password');
		$this->apiUrl = config('sms-mts.api_urls.basic');
	}

	public function send(array $payload)
	{
		return Http::withBasicAuth($this->login, $this->password)
			->timeout(config('sms-mts.timeout'))
			->post($this->apiUrl, $payload);
	}
}
