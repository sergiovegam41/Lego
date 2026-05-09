<?php

namespace Core\Services\Graph;

abstract class AbstractOpenAiProvider implements LlmProvider
{
    protected const MAX_CODE_CHARS = 12000;
    protected const MAX_RETRIES    = 4;

    public function __construct(protected readonly string $model) {}

    public function getModel(): string
    {
        return $this->model;
    }

    abstract public function getProviderName(): string;

    abstract protected function getEndpoint(): string;

    /**
     * Cabeceras adicionales (auth, etc.). Content-Type ya está incluido.
     */
    protected function getHeaders(): array
    {
        return [];
    }

    protected function getTimeout(): int
    {
        return 30;
    }

    public function describe(string $shortName, string $type, string $relativePath, string $code): ?string
    {
        $code = $this->truncate($code);
        $userPrompt = <<<PROMPT
NOMBRE: {$shortName}
TIPO: {$type}
ARCHIVO: {$relativePath}

CÓDIGO:
```php
{$code}
```

Describí su responsabilidad funcional en una sola oración (max 200 chars).
PROMPT;

        $response = $this->complete(
            systemPrompt: PromptLibrary::load('short-description'),
            userPrompt: $userPrompt,
            maxTokens: 200
        );

        return $response !== null ? $this->cleanShortResponse($response) : null;
    }

    public function describeDeep(array $context, string $code): ?string
    {
        $code        = $this->truncate($code);
        $userPrompt  = $this->buildDeepUserPrompt($context, $code);

        $response = $this->complete(
            systemPrompt: PromptLibrary::load('deep-analysis'),
            userPrompt: $userPrompt,
            maxTokens: 1500,
            temperature: 0.3
        );

        if ($response === null) {
            return null;
        }

        return trim($response);
    }

    protected function complete(string $systemPrompt, string $userPrompt, int $maxTokens, float $temperature = 0.2): ?string
    {
        $payload = [
            'model'       => $this->model,
            'temperature' => $temperature,
            'max_tokens'  => $maxTokens,
            'messages'    => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user',   'content' => $userPrompt],
            ],
        ];

        for ($attempt = 0; $attempt <= self::MAX_RETRIES; $attempt++) {
            [$response, $retryAfter] = $this->call($payload);
            if ($response !== null) {
                return $response;
            }
            if ($attempt < self::MAX_RETRIES) {
                $waitSeconds = $retryAfter ?? min(2 ** $attempt, 8);
                sleep($waitSeconds);
            }
        }

        return null;
    }

    /**
     * @return array{0: ?string, 1: ?int}  [content, retryAfterSeconds]
     */
    protected function call(array $payload): array
    {
        $responseHeaders = [];
        $headers = array_merge(['Content-Type: application/json'], $this->getHeaders());

        $ch = curl_init($this->getEndpoint());
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => $this->getTimeout(),
            CURLOPT_HEADERFUNCTION => function ($curl, $header) use (&$responseHeaders) {
                $len = strlen($header);
                $parts = explode(':', $header, 2);
                if (count($parts) === 2) {
                    $responseHeaders[strtolower(trim($parts[0]))] = trim($parts[1]);
                }
                return $len;
            },
        ]);

        $body   = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false) {
            return [null, null];
        }

        if ($status === 429) {
            $retryAfter = isset($responseHeaders['retry-after'])
                ? (int) ceil((float) $responseHeaders['retry-after'])
                : null;
            return [null, $retryAfter];
        }

        if ($status >= 400) {
            return [null, null];
        }

        $decoded = json_decode($body, true);
        return [$decoded['choices'][0]['message']['content'] ?? null, null];
    }

    protected function buildDeepUserPrompt(array $context, string $code): string
    {
        $shortName        = $context['short_name'];
        $type             = $context['type'];
        $file             = $context['file'];
        $extends          = $context['extends']          ?? 'ninguna';
        $implements       = $context['implements']       ?? 'ninguna';
        $traits           = $context['traits']           ?? 'ninguno';
        $attributes       = $context['attributes']       ?? 'ninguno';
        $extendedBy       = $context['extended_by']      ?? 'ninguna';
        $implementedBy    = $context['implemented_by']   ?? 'ninguna';
        $usedAsTraitBy    = $context['used_as_trait_by'] ?? 'ninguna';
        $instantiatedBy   = $context['instantiated_by']  ?? 'ninguna';
        $depsCount        = $context['deps']             ?? 0;
        $dependents       = $context['dependents']       ?? 0;
        $shortDesc        = $context['short_description'] ?? '(sin descripción previa)';

        return <<<PROMPT
NOMBRE: {$shortName}
TIPO: {$type}
ARCHIVO: {$file}
DESCRIPCIÓN CORTA: {$shortDesc}

== Relaciones SALIENTES (lo que esta clase usa) ==
HEREDA: {$extends}
IMPLEMENTA: {$implements}
USA TRAITS: {$traits}
ATRIBUTOS: {$attributes}

== Relaciones ENTRANTES (clases reales del codebase que dependen de esta) ==
EXTENDIDA POR: {$extendedBy}
IMPLEMENTADA POR: {$implementedBy}
USADA COMO TRAIT POR: {$usedAsTraitBy}
INSTANCIADA POR: {$instantiatedBy}

TOTAL DEPS: {$depsCount}
TOTAL DEPENDENTS: {$dependents}

CÓDIGO:
```php
{$code}
```

IMPORTANTE: usá EXCLUSIVAMENTE los nombres de clases que aparecen arriba en las relaciones. NO inventes clases que no estén listadas. Si no hay clases en una relación, no las menciones.

Generá el análisis profundo siguiendo la estructura definida en el system prompt.
PROMPT;
    }

    protected function cleanShortResponse(string $raw): string
    {
        $text = trim($raw);
        $text = preg_replace('/^["\'`]+|["\'`]+$/u', '', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        if (strpos($text, "\n") !== false) {
            $text = substr($text, 0, strpos($text, "\n"));
        }
        if (mb_strlen($text) > 240) {
            $text = mb_substr($text, 0, 237) . '...';
        }
        return $text;
    }

    protected function truncate(string $code): string
    {
        if (strlen($code) <= self::MAX_CODE_CHARS) {
            return $code;
        }
        return substr($code, 0, self::MAX_CODE_CHARS) . "\n// ... (archivo truncado)";
    }
}
