<?php

declare(strict_types=1);

namespace Tests\Fixtures\Components;

use Lego\View\Component;
use Tests\Fixtures\Components\Badge\Badge;

final class Card extends Component
{
    protected array $styles = ['card.css'];

    protected array $scripts = ['card.js'];

    public function __construct(private readonly string $title, private readonly string $badge = 'nuevo')
    {
    }

    protected function render(): string
    {
        $title = self::e($this->title);
        $badge = new Badge($this->badge);

        return "<article class=\"card\"><h2>{$title}</h2>{$badge}</article>";
    }
}
