<?php

declare(strict_types=1);

namespace Lego\View;

/**
 * Turns components into the two HTML shapes Lego serves.
 *
 * - page(): a full document for the Page layer.
 * - fragment(): asset tags plus markup for the Component layer, which the shell
 *   inserts without reloading.
 */
final class Document
{
    /** @param string $head Extra trusted markup for <head>, placed before asset tags (e.g. an import map). */
    public static function page(string $title, Component|string $body, string $lang = 'es', string $csrfToken = '', string $head = ''): string
    {
        [$html, $assets] = Assets::collect(static fn (): string => (string) $body);
        $title = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $lang = htmlspecialchars($lang, ENT_QUOTES);
        $tags = $assets->tags();
        $csrf = $csrfToken === '' ? '' : '<meta name="csrf-token" content="' . htmlspecialchars($csrfToken, ENT_QUOTES) . '">';

        return <<<HTML
            <!doctype html>
            <html lang="{$lang}">
            <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta name="color-scheme" content="light dark">
            <title>{$title}</title>
            {$csrf}
            {$head}
            {$tags}
            </head>
            <body>
            {$html}
            </body>
            </html>
            HTML;
    }

    public static function fragment(Component|string $body): string
    {
        [$html, $assets] = Assets::collect(static fn (): string => (string) $body);
        $tags = $assets->tags();

        return $tags === '' ? $html : $tags . "\n" . $html;
    }
}
