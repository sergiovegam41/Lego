<?php

declare(strict_types=1);

namespace Lego\Ui\Records;

use Lego\View\Component;

/**
 * Result screen for an action that returns a message the user must read,
 * such as a generated password. Used when the form was posted without
 * JavaScript; with JavaScript the same message appears in a dialog.
 */
final class ActionMessage extends Component
{
    protected array $styles = ['records.css'];

    public function __construct(private readonly string $message, private readonly string $backUrl)
    {
    }

    protected function render(): string
    {
        $message = self::e($this->message);
        $back = self::e($this->backUrl);

        return <<<HTML
            <section class="lego-records">
              <h1>Hecho</h1>
              <p role="status">{$message}</p>
              <div class="lego-records__buttons"><a class="lego-button" href="{$back}">Volver</a></div>
            </section>
            HTML;
    }
}
