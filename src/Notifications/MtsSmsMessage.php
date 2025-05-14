<?php

namespace Rutrue\MtsSms\Notifications;

class MtsSmsMessage
{
    public function __construct(
        public string $content,
        public array $params = []
    ) {
    }
}