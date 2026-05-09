<?php

namespace Core\Services\Graph;

class GroqProvider extends AbstractOpenAiProvider
{
    private const ENDPOINT = 'https://api.groq.com/openai/v1/chat/completions';

    public function __construct(
        private readonly string $apiKey,
        string $model = 'llama-3.3-70b-versatile'
    ) {
        parent::__construct($model);
    }

    public function getProviderName(): string
    {
        return 'groq';
    }

    protected function getEndpoint(): string
    {
        return self::ENDPOINT;
    }

    protected function getHeaders(): array
    {
        return ['Authorization: Bearer ' . $this->apiKey];
    }
}
