<?php

declare(strict_types=1);

namespace Lego\Ui\Layout;

use Lego\Auth\Identity;
use Lego\Http\Csrf;
use Lego\View\Component;

/**
 * Page frame for signed-in users: header with the menu and the session, and
 * the screen in the main area. Phase 4 turns it into the shell with open
 * modules; the contract (menu items + content component) stays.
 */
final class Layout extends Component
{
    protected array $styles = ['layout.css'];

    /** @param list<array{name: string, label: string, icon: string, url: string, active: bool}> $menu */
    public function __construct(
        private readonly Identity $user,
        private readonly array $menu,
        private readonly string $csrfToken,
        private readonly Component $content,
    ) {
    }

    protected function render(): string
    {
        $items = '';
        foreach ($this->menu as $item) {
            $current = $item['active'] ? ' aria-current="page"' : '';
            $items .= '<li><a href="' . self::e($item['url']) . '"' . $current . ' data-module="' . self::e($item['name']) . '">'
                . self::e($item['label']) . '</a></li>';
        }

        $name = self::e($this->user->name);
        $field = Csrf::FIELD;
        $csrf = self::e($this->csrfToken);

        return <<<HTML
            <div class="lego-layout">
              <header class="lego-layout__header">
                <a class="lego-layout__brand" href="/">Lego</a>
                <nav class="lego-layout__menu" aria-label="Módulos"><ul>{$items}</ul></nav>
                <form class="lego-layout__session" method="post" action="/logout">
                  <span>{$name}</span>
                  <input type="hidden" name="{$field}" value="{$csrf}">
                  <button type="submit">Cerrar sesión</button>
                </form>
              </header>
              <main class="lego-layout__main">{$this->content}</main>
            </div>
            HTML;
    }
}
