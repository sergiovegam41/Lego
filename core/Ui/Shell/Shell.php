<?php

declare(strict_types=1);

namespace Lego\Ui\Shell;

use Lego\Auth\Identity;
use Lego\Http\Csrf;
use Lego\View\Assets;
use Lego\View\Component;

/**
 * The application frame for signed-in users (phase 4).
 *
 * The server renders the active screen inside the outlet, so the page works
 * on first load, with F5 and without JavaScript. lego.js then takes over:
 * menu links and links inside screens open screens without reloading, open
 * screens stay mounted as tabs, and the URL always names the active one.
 */
final class Shell extends Component
{
    protected array $styles = ['../Theme/tokens.css', 'shell.css'];

    protected array $scripts = ['../js/lego.js'];

    /** @param list<array{name: string, label: string, icon: string, url: string, active: bool}> $menu */
    public function __construct(
        private readonly Identity $user,
        private readonly array $menu,
        private readonly string $csrfToken,
        private readonly Screen $screen,
    ) {
    }

    /**
     * Import map that lets ES modules write `import { api } from 'lego'` and
     * `import('lego/ui/table.js')` with no build step (decision B10).
     */
    public static function importMap(): string
    {
        $core = Assets::url(__DIR__ . '/../js/lego.js');
        $map = ['imports' => ['lego' => $core, 'lego/' => dirname(strtok($core, '?') ?: $core) . '/']];

        return '<script type="importmap">' . json_encode($map, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) . '</script>';
    }

    protected function render(): string
    {
        $items = '';
        foreach ($this->menu as $item) {
            $current = $item['active'] ? ' aria-current="page"' : '';
            $items .= '<li><a href="' . self::e($item['url']) . '" data-lego-nav data-module="' . self::e($item['name']) . '"' . $current . '>'
                . self::e($item['label']) . '</a></li>';
        }

        $name = self::e($this->user->name);
        $field = Csrf::FIELD;
        $csrf = self::e($this->csrfToken);
        $homeCurrent = $this->screen->key === 'home' ? ' aria-current="page"' : '';

        return <<<HTML
            <div class="lego-shell">
              <aside class="lego-shell__sidebar">
                <a class="lego-shell__brand" href="/" data-lego-nav{$homeCurrent}>Lego</a>
                <nav class="lego-shell__menu" aria-label="Módulos"><ul>{$items}</ul></nav>
              </aside>
              <header class="lego-shell__header">
                <nav class="lego-shell__tabs" data-lego-tabs aria-label="Pantallas abiertas"></nav>
                <div class="lego-shell__session">
                  <button class="lego-button" type="button" data-lego-theme-toggle aria-label="Cambiar tema">◐</button>
                  <span>{$name}</span>
                  <form method="post" action="/logout">
                    <input type="hidden" name="{$field}" value="{$csrf}">
                    <button class="lego-button" type="submit">Cerrar sesión</button>
                  </form>
                </div>
              </header>
              <main class="lego-shell__outlet" data-lego-outlet>{$this->screen}</main>
            </div>
            HTML;
    }
}
