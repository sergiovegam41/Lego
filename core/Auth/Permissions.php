<?php

declare(strict_types=1);

namespace Lego\Auth;

use InvalidArgumentException;

/**
 * Permission names are "<module>.<operation>", e.g. "clients.create" (decision D2).
 *
 * A role grants:
 * - "*"           every permission,
 * - "clients.*"   every operation of one module,
 * - "clients.create" exactly one permission.
 */
final class Permissions
{
    private const PATTERN = '/^[a-z][a-z0-9_]*\.[a-z][a-z0-9_]*$/';

    /** @param list<string> $granted */
    public static function grants(array $granted, string $permission): bool
    {
        self::assertValid($permission);
        [$module] = explode('.', $permission, 2);

        foreach ($granted as $grant) {
            if ($grant === '*' || $grant === $permission || $grant === $module . '.*') {
                return true;
            }
        }

        return false;
    }

    public static function isValid(string $permission): bool
    {
        return preg_match(self::PATTERN, $permission) === 1;
    }

    public static function assertValid(string $permission): void
    {
        if (!self::isValid($permission)) {
            throw new InvalidArgumentException("Invalid permission name \"{$permission}\"; expected <module>.<operation>.");
        }
    }
}
