<?php

declare(strict_types=1);

namespace Tests\Support;

use Lego\Auth\Guard;
use Lego\Auth\Identity;
use Lego\Http\Request;

/** Guard that always returns the given identity, for unit tests. */
final class FakeGuard implements Guard
{
    public function __construct(private readonly ?Identity $identity)
    {
    }

    /** @param list<string> $permissions */
    public static function as(array $permissions = ['*'], string $name = 'Ana'): self
    {
        return new self(new Identity(1, $name, 'ana@example.com', 'admin', $permissions));
    }

    public function identify(Request $request): ?Identity
    {
        return $this->identity;
    }
}
