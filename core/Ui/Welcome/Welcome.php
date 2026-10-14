<?php

declare(strict_types=1);

namespace Lego\Ui\Welcome;

use Lego\View\Component;

/** Landing screen shown while Lego 2 has no modules. */
final class Welcome extends Component
{
    protected array $styles = ['welcome.css'];

    public function __construct(private readonly string $version)
    {
    }

    protected function render(): string
    {
        $version = self::e($this->version);

        return <<<HTML
            <main class="lego-welcome">
              <h1>Lego</h1>
              <p>Componentes, módulos declarativos y acciones.</p>
              <p class="lego-welcome__version">v{$version}</p>
            </main>
            HTML;
    }
}
