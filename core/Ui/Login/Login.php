<?php

declare(strict_types=1);

namespace Lego\Ui\Login;

use Lego\Http\Csrf;
use Lego\View\Component;

/** Sign-in form. A plain HTML form: it works without JavaScript. */
final class Login extends Component
{
    protected array $styles = ['login.css'];

    public function __construct(
        private readonly string $csrfToken,
        private readonly string $next = '/',
        private readonly string $email = '',
        private readonly ?string $error = null,
    ) {
    }

    protected function render(): string
    {
        $field = Csrf::FIELD;
        $csrf = self::e($this->csrfToken);
        $next = self::e($this->next);
        $email = self::e($this->email);
        $error = $this->error === null
            ? ''
            : '<p class="lego-login__error" role="alert">' . self::e($this->error) . '</p>';

        return <<<HTML
            <main class="lego-login">
              <form class="lego-login__card" method="post" action="/login">
                <h1>Lego</h1>
                {$error}
                <input type="hidden" name="{$field}" value="{$csrf}">
                <input type="hidden" name="next" value="{$next}">
                <label>Correo
                  <input type="email" name="email" value="{$email}" autocomplete="username" required autofocus>
                </label>
                <label>Contraseña
                  <input type="password" name="password" autocomplete="current-password" required>
                </label>
                <button type="submit">Iniciar sesión</button>
              </form>
            </main>
            HTML;
    }
}
