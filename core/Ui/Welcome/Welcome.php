<?php

declare(strict_types=1);

namespace Lego\Ui\Welcome;

use Lego\Ui\Brand\Logo;
use Lego\View\Component;

/** Home screen: a greeting until modules provide a dashboard. */
final class Welcome extends Component
{
    protected array $styles = ['welcome.css'];

    public function __construct(private readonly string $version, private readonly string $userName)
    {
    }

    protected function render(): string
    {
        $version = self::e($this->version);
        $name = self::e($this->userName);
        $logo = new Logo();

        return <<<HTML
            <section class="lego-welcome">
              <h1>{$logo}</h1>
              <p>Hola, {$name}.</p>
              <p>Componentes, módulos declarativos y acciones.</p>
              <p class="lego-welcome__version">v{$version}</p>
            </section>
            HTML;
    }
}
