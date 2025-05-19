<?php

namespace RuTrue\SmsMts\Notifications;

class SmsMtsMessage
{
	public string $content;
	public array $params = [];

	public function __construct(string $content)
	{
		$this->content = $content;
	}

	public function params(array $params): self
	{
		$this->params = $params;
		return $this;
	}
}
