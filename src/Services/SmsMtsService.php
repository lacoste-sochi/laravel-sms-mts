<?php

namespace RuTrue\SmsMts\Services;

use RuTrue\SmsMts\Contracts\SmsMtsDriverInterface;
use RuTrue\SmsMts\Exceptions\SmsMtsException;
use RuTrue\SmsMts\Exceptions\SmsMtsConfigurationException;
use RuTrue\SmsMts\Services\Auth\BasicAuth;
use RuTrue\SmsMts\Services\Auth\TokenAuth;

class SmsMtsService implements SmsMtsDriverInterface
{
	protected string $authMethod;
	protected $authService;
	protected string $sender;

	public function __construct()
	{
		$this->validateConfig();
		$this->authMethod = config('sms-mts.auth_method');
		$this->sender = config('sms-mts.sender');
		$this->initAuthService();
	}

	public function send(string $phone, string $message, array $params = [])
	{
		$payload = $this->buildPayload(
			[$this->normalizePhone($phone)],
			$message,
			$params
		);

		return $this->sendRequest($payload);
	}

	public function bulkSend(array $messages)
	{
		if (empty($messages)) {
			throw new SmsMtsException('Messages list cannot be empty');
		}

		// Создаем отдельные payload для каждого сообщения
		$payloads = array_map(function ($message) {
			if (!isset($message['phone']) || !isset($message['message'])) {
				throw new SmsMtsException('Each message must contain "phone" and "message" keys');
			}

			return $this->buildPayload(
				[$this->normalizePhone($message['phone'])],
				$message['message'],
				$message['params'] ?? []
			);
		}, $messages);

		// Объединяем payloads в зависимости от метода аутентификации
		$combinedPayload = $this->combinePayloads($payloads);

		return $this->sendRequest($combinedPayload);
	}

	protected function combinePayloads(array $payloads): array
	{
		if ($this->authMethod === 'token') {
			return [
				'naming' => $this->sender,
				'submits' => array_merge(
					...array_column($payloads, 'submits')
				)
			];
		}

		return [
			'messages' => array_merge(
				...array_column($payloads, 'messages')
			),
			'options' => $payloads[0]['options'] ?? [
				'from' => ['sms_address' => $this->sender]
			]
		];
	}

	protected function sendRequest(array $payload)
	{
		$response = $this->authService->send($payload);

		// Убедимся, что $response - это объект Response
		if ($response instanceof \Illuminate\Http\Client\Response) {
			if ($response->failed()) {
				$error = $response->json();
				throw new SmsMtsException(
					$error['description'] ?? 'MTS SMS API Error',
					$response->status(),
					$error['code'] ?? null
				);
			}
			return $response->json();
		}

		// Если ответ уже массив (для тестовых моков)
		if (is_array($response)) {
			if (isset($response['error'])) {
				throw new SmsMtsException(
					$response['description'] ?? 'MTS SMS API Error',
					$response['status'] ?? 500,
					$response['code'] ?? null
				);
			}
			return $response;
		}

		throw new SmsMtsException('Invalid response type from MTS API');
	}

	protected function buildPayload(array $phones, string $message, array $params = []): array
	{
		if ($this->authMethod === 'token') {
			return [
				'naming' => $this->sender,
				'submits' => array_map(function ($phone) use ($message, $params) {
					return array_filter([
						'msid' => $phone,
						'message' => $message,
						'ttl' => $params['ttl'] ?? null,
					]);
				}, $phones),
			];
		}

		return [
			'messages' => [
				[
					'content' => ['short_text' => $message],
					'to' => array_map(function ($phone) {
						return ['msisdn' => $phone];
					}, $phones),
				],
			],
			'options' => [
				'from' => ['sms_address' => $this->sender],
			],
		];
	}

	protected function normalizePhone(string $phone): string
	{
		$phone = preg_replace('/[^0-9]/', '', $phone);

		if (str_starts_with($phone, '8')) {
			$phone = '7' . substr($phone, 1);
		}

		if (!preg_match('/^7\d{10}$/', $phone)) {
			throw new SmsMtsException("Invalid phone number format: {$phone}");
		}

		return $phone;
	}

	protected function validateConfig(): void
	{
		if (!in_array(config('sms-mts.auth_method'), ['token', 'basic'])) {
			throw new SmsMtsConfigurationException('Invalid MTS SMS auth method');
		}

		if (config('sms-mts.auth_method') === 'token' && empty(config('sms-mts.token'))) {
			throw new SmsMtsConfigurationException('MTS SMS token is required');
		}

		if (config('sms-mts.auth_method') === 'basic' && (empty(config('sms-mts.login')) || empty(config('sms-mts.password')))) {
			throw new SmsMtsConfigurationException('MTS SMS login and password are required');
		}

		if (empty(config('sms-mts.sender'))) {
			throw new SmsMtsConfigurationException('MTS SMS sender name is required');
		}
	}

	protected function initAuthService(): void
	{
		$this->authService = match ($this->authMethod) {
			'token' => new TokenAuth(),
			'basic' => new BasicAuth(),
			default => throw new SmsMtsConfigurationException('Unsupported auth method'),
		};
	}
}
