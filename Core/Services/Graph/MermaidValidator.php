<?php

namespace Core\Services\Graph;

class MermaidValidator
{
    private const VALID_TYPES = [
        'graph',
        'flowchart',
        'sequenceDiagram',
        'classDiagram',
        'stateDiagram',
        'stateDiagram-v2',
        'erDiagram',
        'gantt',
        'pie',
        'journey',
    ];

    /**
     * Valida la sintaxis básica de un bloque Mermaid.
     *
     * Devuelve {valid: bool, reason?: string}.
     */
    public function validate(string $mermaidCode): array
    {
        $code = trim($mermaidCode);
        if ($code === '') {
            return ['valid' => false, 'reason' => 'bloque vacío'];
        }

        $firstLine = trim(strtok($code, "\n"));
        $hasValidType = false;
        foreach (self::VALID_TYPES as $type) {
            if (str_starts_with($firstLine, $type)) {
                $hasValidType = true;
                break;
            }
        }
        if (!$hasValidType) {
            return ['valid' => false, 'reason' => "tipo de diagrama desconocido: '{$firstLine}'"];
        }

        if (!$this->bracketsBalanced($code)) {
            return ['valid' => false, 'reason' => 'paréntesis o corchetes desbalanceados'];
        }

        if ($this->mixesDiagramTypes($code)) {
            return ['valid' => false, 'reason' => 'mezcla múltiples tipos de diagrama'];
        }

        return ['valid' => true];
    }

    /**
     * Procesa el output del LLM, extrae el bloque Mermaid si existe,
     * lo valida y lo descarta si es inválido.
     *
     * @return string  El markdown final con el bloque mermaid si era válido,
     *                 o sin la sección "Diagrama" si no.
     */
    public function processResponse(string $markdown): string
    {
        if (!preg_match('/```mermaid\s*\n(.*?)\n```/s', $markdown, $matches)) {
            return $markdown;
        }

        $mermaidCode = $matches[1];
        $result      = $this->validate($mermaidCode);

        if ($result['valid']) {
            return $markdown;
        }

        return $this->stripDiagramSection($markdown);
    }

    private function bracketsBalanced(string $code): bool
    {
        $pairs = ['(' => ')', '[' => ']', '{' => '}'];
        $opens = array_keys($pairs);
        $stack = [];

        $inString = false;
        $stringChar = '';
        $len = strlen($code);

        for ($i = 0; $i < $len; $i++) {
            $ch = $code[$i];

            if ($inString) {
                if ($ch === $stringChar && ($i === 0 || $code[$i - 1] !== '\\')) {
                    $inString = false;
                }
                continue;
            }

            if ($ch === '"' || $ch === "'") {
                $inString = true;
                $stringChar = $ch;
                continue;
            }

            if (in_array($ch, $opens, true)) {
                $stack[] = $pairs[$ch];
                continue;
            }

            if (in_array($ch, array_values($pairs), true)) {
                if (empty($stack) || array_pop($stack) !== $ch) {
                    return false;
                }
            }
        }

        return empty($stack);
    }

    private function mixesDiagramTypes(string $code): bool
    {
        $found = 0;
        foreach (self::VALID_TYPES as $type) {
            if (preg_match('/^\s*' . preg_quote($type, '/') . '\b/m', $code)) {
                $found++;
            }
        }
        return $found > 1;
    }

    private function stripDiagramSection(string $markdown): string
    {
        $lines  = explode("\n", $markdown);
        $output = [];
        $skip   = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if (preg_match('/^###\s+Diagrama\s*$/i', $trimmed)) {
                $skip = true;
                continue;
            }

            if ($skip && preg_match('/^###\s+/', $trimmed)) {
                $skip = false;
            }

            if (!$skip) {
                $output[] = $line;
            }
        }

        $result = implode("\n", $output);
        return preg_replace("/\n{3,}/", "\n\n", $result);
    }
}
