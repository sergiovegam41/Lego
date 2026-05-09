<?php

namespace Core\Services\Graph;

interface LlmProvider
{
    public function getModel(): string;

    public function getProviderName(): string;

    /**
     * Genera una descripción corta (1 oración técnica) de una clase PHP.
     */
    public function describe(string $shortName, string $type, string $relativePath, string $code): ?string;

    /**
     * Genera un análisis técnico profundo en markdown con diagrama Mermaid opcional.
     *
     * @param array{
     *   short_name: string,
     *   type: string,
     *   file: string,
     *   extends?: string,
     *   implements?: string,
     *   traits?: string,
     *   attributes?: string,
     *   deps?: int,
     *   dependents?: int,
     *   short_description?: string
     * } $context
     */
    public function describeDeep(array $context, string $code): ?string;
}
