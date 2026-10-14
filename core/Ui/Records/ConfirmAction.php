<?php

declare(strict_types=1);

namespace Lego\Ui\Records;

use Lego\Http\Csrf;
use Lego\View\Component;

/**
 * Second step of a destructive action: shows the server's message and posts
 * the same request again with the single-use confirmation token.
 */
final class ConfirmAction extends Component
{
    protected array $styles = ['records.css'];

    /** @param array<string, string> $input Original form values to resend. */
    public function __construct(
        private readonly string $message,
        private readonly string $action,
        private readonly string $token,
        private readonly string $csrfToken,
        private readonly string $cancelUrl,
        private readonly array $input = [],
    ) {
    }

    protected function render(): string
    {
        $hidden = '<input type="hidden" name="' . Csrf::FIELD . '" value="' . self::e($this->csrfToken) . '">'
            . '<input type="hidden" name="confirmation_token" value="' . self::e($this->token) . '">';
        foreach ($this->input as $name => $value) {
            $hidden .= '<input type="hidden" name="' . self::e($name) . '" value="' . self::e($value) . '">';
        }

        $message = self::e($this->message);
        $action = self::e($this->action);
        $cancel = self::e($this->cancelUrl);

        return <<<HTML
            <section class="lego-records lego-records--confirm">
              <h1>Confirmar</h1>
              <p>{$message}</p>
              <form method="post" action="{$action}">
                {$hidden}
                <div class="lego-records__buttons">
                  <button class="lego-button lego-button--danger" type="submit">Confirmar</button>
                  <a class="lego-button" href="{$cancel}">Cancelar</a>
                </div>
              </form>
            </section>
            HTML;
    }
}
