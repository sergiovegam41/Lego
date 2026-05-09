<?php

namespace Core\Services\Graph;

class OllamaProvider extends AbstractOpenAiProvider
{
    public function __construct(
        private readonly string $host,
        string $model = 'qwen2.5-coder:14b'
    ) {
        parent::__construct($model);
    }

    public function getProviderName(): string
    {
        return 'ollama';
    }

    protected function getEndpoint(): string
    {
        return rtrim($this->host, '/') . '/v1/chat/completions';
    }

    protected function getTimeout(): int
    {
        return 120;
    }
}
