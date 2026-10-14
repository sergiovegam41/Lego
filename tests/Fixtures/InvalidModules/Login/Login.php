<?php

declare(strict_types=1);

namespace Tests\Fixtures\InvalidModules\Login;

use Lego\Module\Module;
use Lego\View\Component;

/** Invalid on purpose: "login" collides with a core route. */
#[Module(label: 'Login')]
final class Login extends Component
{
    protected function render(): string
    {
        return '';
    }
}
