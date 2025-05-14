<?php

namespace Rutrue\MtsSms\Contracts;

interface MtsSmsDriverInterface
{
    public function send(string $phone, string $message, array $params = []);
    public function bulkSend(array $messages);
}