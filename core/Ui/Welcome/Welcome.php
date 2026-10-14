<?php

declare(strict_types=1);

namespace Lego\Ui\Welcome;

use Lego\Auth\Identity;
use Lego\Http\Csrf;
use Lego\View\Component;

/** Landing screen shown while Lego 2 has no modules. */
final class Welcome extends Component
{
    protected array $styles = ['welcome.css'];

    public function __construct(
        private readonly string $version,
        private readonly ?Identity $user = null,
        private readonly string $csrfToken = '',
    ) {
    }

    protected function render(): string
    {
        $version = self::e($this->version);
        $session = '';
        if ($this->user !== null) {
            $name = self::e($this->user->name);
            $field = Csrf::FIELD;
            $csrf = self::e($this->csrfToken);
            $session = <<<HTML
                <p>Hola, {$name}.</p>
                <form method="post" action="/logout">
                  <input type="hidden" name="{$field}" value="{$csrf}">
                  <button type="submit">Cerrar sesión</button>
                </form>
                HTML;
        }

        return <<<HTML
            <main class="lego-welcome">
              <h1>Lego</h1>
              <p>Componentes, módulos declarativos y acciones.</p>
              {$session}
              <p class="lego-welcome__version">v{$version}</p>
            </main>
            HTML;
    }
}
