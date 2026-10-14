<?php

declare(strict_types=1);

namespace Lego\Auth;

/**
 * The signed-in user as the rest of the framework sees it: plain data, no
 * database access. Built by the session guard on every authenticated request.
 */
final readonly class Identity
{
    /** @param list<string> $permissions */
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public string $role,
        public array $permissions,
        public ?string $sessionId = null,
    ) {
    }

    public function can(string $permission): bool
    {
        return Permissions::grants($this->permissions, $permission);
    }
}
