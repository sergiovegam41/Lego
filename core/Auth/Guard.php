<?php

declare(strict_types=1);

namespace Lego\Auth;

use Lego\Http\Request;

/** Resolves the signed-in user of a request. */
interface Guard
{
    public function identify(Request $request): ?Identity;
}
