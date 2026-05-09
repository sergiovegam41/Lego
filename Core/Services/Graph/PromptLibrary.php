<?php

namespace Core\Services\Graph;

class PromptLibrary
{
    private const PROMPTS_DIR = __DIR__ . '/Prompts';

    /**
     * Carga el contenido de un skill markdown.
     *
     * @param string $skillName  ej: "short-description", "deep-analysis"
     */
    public static function load(string $skillName): string
    {
        $path = self::PROMPTS_DIR . DIRECTORY_SEPARATOR . $skillName . '.md';
        if (!file_exists($path)) {
            throw new \RuntimeException("Skill no encontrado: {$skillName} (esperado en {$path})");
        }
        return file_get_contents($path);
    }
}
