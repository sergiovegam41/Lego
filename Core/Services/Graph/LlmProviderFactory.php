<?php

namespace Core\Services\Graph;

class LlmProviderFactory
{
    /**
     * Crea el LlmProvider según las variables de entorno.
     *
     * Variables relevantes:
     * - LLM_PROVIDER     ollama | groq      (default: ollama)
     * - GROQ_API_KEY     (requerido si provider=groq)
     * - GROQ_MODEL       (default: llama-3.3-70b-versatile)
     * - OLLAMA_HOST      (default: http://host.docker.internal:11434)
     * - OLLAMA_MODEL     (default: qwen2.5-coder:14b)
     */
    public static function create(): LlmProvider
    {
        $provider = strtolower(self::env('LLM_PROVIDER', 'ollama'));

        return match ($provider) {
            'groq'   => self::makeGroq(),
            'ollama' => self::makeOllama(),
            default  => throw new \RuntimeException("LLM_PROVIDER desconocido: '{$provider}'. Valores válidos: ollama, groq."),
        };
    }

    private static function makeGroq(): GroqProvider
    {
        $apiKey = self::env('GROQ_API_KEY');
        if (!$apiKey) {
            throw new \RuntimeException("Falta GROQ_API_KEY en .env (LLM_PROVIDER=groq lo requiere).");
        }
        $model = self::env('GROQ_MODEL', 'llama-3.3-70b-versatile');
        return new GroqProvider($apiKey, $model);
    }

    private static function makeOllama(): OllamaProvider
    {
        $host  = self::env('OLLAMA_HOST', 'http://host.docker.internal:11434');
        $model = self::env('OLLAMA_MODEL', 'qwen2.5-coder:14b');
        return new OllamaProvider($host, $model);
    }

    private static function env(string $key, ?string $default = null): ?string
    {
        $value = $_ENV[$key] ?? getenv($key) ?: null;
        return $value ?: $default;
    }
}
