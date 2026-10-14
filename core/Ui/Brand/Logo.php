<?php

declare(strict_types=1);

namespace Lego\Ui\Brand;

use Lego\View\Component;

/**
 * The Lego mark: three placed modules and one you add (doc/identidad.md).
 *
 * A 9 × 9 grid of four 4-unit cells with a 1-unit gap; the accent cell always
 * sits bottom right. The name follows at 3 units. Size follows font-size.
 */
final class Logo extends Component
{
    protected array $styles = ['logo.css'];

    public function __construct(private readonly bool $withName = true)
    {
    }

    protected function render(): string
    {
        $name = $this->withName ? '<span class="lego-logo__name">Lego</span>' : '';
        $label = $this->withName ? '' : ' role="img" aria-label="Lego"';

        return <<<HTML
            <span class="lego-logo"{$label}><svg class="lego-logo__mark" viewBox="0 0 9 9" aria-hidden="true"><rect width="4" height="4"/><rect x="5" width="4" height="4"/><rect y="5" width="4" height="4"/><rect class="lego-logo__accent" x="5" y="5" width="4" height="4"/></svg>{$name}</span>
            HTML;
    }
}
