<?php

namespace Rutrue\MtsSms\Services;

use Rutrue\MtsSms\Contracts\MtsSmsDriverInterface;
use Illuminate\Support\Facades\Http;

class MtsSmsService implements MtsSmsDriverInterface
{
    public function __construct(
        private string $apiKey,
        private string $apiUrl,
        private string $senderName
    ) {
    }

    public function send(string $phone, string $message, array $params = [])
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->post($this->apiUrl . '/send', [
                    'sender' => $this->senderName,
                    'recipient' => $phone,
                    'message' => $message,
                    ...$params
                ]);

        if ($response->failed()) {
            throw new \Rutrue\MtsSms\Exceptions\MtsSmsException($response->body());
        }

        return $response->json();
    }

    public function bulkSend(array $messages)
    {
        // Реализация массовой отправки
    }
}