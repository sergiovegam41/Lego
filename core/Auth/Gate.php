<?php

declare(strict_types=1);

namespace Lego\Auth;

use Lego\Errors\Forbidden;
use Lego\Errors\Unauthenticated;

/**
 * The single authorization point of Lego (decision D2).
 *
 * Every check goes through authorize(). It already receives the action input
 * and the record so that per-record rules can be added later without changing
 * any caller.
 */
final class Gate
{
    /**
     * @param array<string, mixed> $input
     *
     * @throws Unauthenticated when there is no user.
     * @throws Forbidden       when the user lacks the permission.
     */
    public function authorize(?Identity $user, string $permission, array $input = [], ?object $record = null): void
    {
        if ($user === null) {
            throw new Unauthenticated();
        }

        if (!$this->allows($user, $permission, $input, $record)) {
            throw new Forbidden();
        }
    }

    /** @param array<string, mixed> $input */
    public function allows(Identity $user, string $permission, array $input = [], ?object $record = null): bool
    {
        return $user->can($permission);
    }
}
