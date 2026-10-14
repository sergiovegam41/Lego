<?php

declare(strict_types=1);

namespace Tests\Fixtures\Components\Badge;

use Lego\View\Component;

final class Badge extends Component
{
    protected array $styles = ['badge.css'];

    public function __construct(private readonly string $label)
    {
    }

    protected function render(): string
    {
        return '<span class="badge">' . self::e($this->label) . '</span>';
    }
}
