<?php

namespace Rutrue\MtsSms\Notifications;

class MtsSmsMessage
{
    public function __construct(
        public string $content,
        public array $params = []
    ) {
    }

    public function content(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function params(array $params): self
    {
        $this->params = $params;
        return $this;
    }
}